<?php

require_once 'Framework/Model.php';
require_once 'Framework/Errors.php';
/**
 * Cache the url informations to speed the routing
 *
 * @author Sylvain Prigent
 */
class FCache extends Model
{
    /**
     * Load the urls into the cach table
     * Call this function to generate the cache
     */
    public function load()
    {
        // URLS
        $this->createTableURL();
        $this->loadUrls();
    }

    /**
     * Implement the URL loading
     */
    public function loadUrls()
    {
        // get the modules list
        $modulesNames = Configuration::get("modules");

        // load each modules
        foreach ($modulesNames as $moduleName) {
            // get the routing class
            $routingClassUrl = "Modules/" . $moduleName . "/" . ucfirst($moduleName) . "Routing.php";
            if (file_exists($routingClassUrl)) {
                $this->addRoutesToDatabase($moduleName, $routingClassUrl);
            } else {
                throw new PfmException("The module '$moduleName' has a not valid routing file", 500);
            }
        }
    }

    /**
     * Get all the route information to add it to the database
     * @param string $moduleName Name of the module
     * @param string $routingClassUrl url of the controller
     */
    protected function addRoutesToDatabase($moduleName, $routingClassUrl)
    {
        require_once($routingClassUrl);
        $className = ucfirst($moduleName) . "Routing";
        $routingClass = new $className();
        $routingClass->listRoutes();
        for ($r = 0; $r < $routingClass->count(); $r++) {
            $identifier = $routingClass->getIdentifier($r);
            $url = $routingClass->getUrl($r);
            $controller = $routingClass->getController($r);
            $actions = $routingClass->getAction($r);
            $gets = $routingClass->getGet($r);
            $getsRegexp = $routingClass->getGetRegexp($r);
            $isApi = $routingClass->isApi($r);

            $this->setCacheUrl($identifier, $url, $moduleName, $controller, $actions, $gets, $getsRegexp, $isApi);
        }
    }

    /**
     * call request methods to add a route to the database
     */
    protected function setCacheUrl($identifier, $url, $module, $controller, $actions, $gets, $getsRegexp, $isApi)
    {
        // insert the urls
        $id = $this->setCacheUrlDB($identifier, $url, $module, $controller, $actions, $isApi);

        // instert the gets
        for ($g = 0; $g < count($gets); $g++) {
            $this->setCacheUrlGetDB($id, $gets[$g], $getsRegexp[$g]);
        }
    }

    /**
     * Request to add a route to the database
     * @return string
     */
    protected function setCacheUrlDB($identifier, $url, $module, $controller, $action, $isApi)
    {

        $id = $this->getCacheUrlID($identifier);
        if ($id > 0) {
            $sql = "UPDATE cache_urls SET identifier=?, url=?, module=?, controller=?, action=?, isapi=? WHERE id=?";
            $this->runRequest($sql, array($identifier, $url, $module, $controller, $action, $isApi, $id));
        } else {
            $sql = "INSERT INTO cache_urls (identifier, url, module, controller, action, isapi) VALUES(?,?,?,?,?,?) ";
            $this->runRequest($sql, array($identifier, $url, $module, $controller, $action, $isApi));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    /**
     * Request to add a route get parameters to the database
     * @return string
     */
    protected function setCacheUrlGetDB($id_url, $name, $regexp)
    {
        $id = $this->getCacheUrlGetID($id_url, $name);
        if ($id > 0) {
            $sql = "UPDATE cache_urls_gets SET `url_id`=?, `name`=?, `regexp`=? WHERE id=?";
            $this->runRequest($sql, array($id_url, $name, $regexp, $id));
        } else {
            $sql = "INSERT INTO cache_urls_gets (`url_id`, `name`, `regexp`) VALUES(?,?,?) ";
            $this->runRequest($sql, array($id_url, $name, $regexp));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    /**
     * get a get parameter cache route id
     * @return string|boolean
     */
    protected function getCacheUrlGetID($id_url, $name)
    {
        $sql = "SELECT id FROM cache_urls_gets WHERE url_id=? AND name=?";
        $req = $this->runRequest($sql, array($id_url, $name));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return false;
    }

    /**
     * get a get parameter cache route id
     * @return string|boolean
     */
    protected function getCacheUrlID($identifier)
    {
        $sql = "SELECT id FROM cache_urls WHERE identifier=?";
        $req = $this->runRequest($sql, array($identifier));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return false;
    }

    /**
     * Remove all the cache
     */
    public function freeTableURL()
    {
        $this->runRequest("SET FOREIGN_KEY_CHECKS = 0")->execute();

        if ($this->isTable("cache_urls"))
            $this->runRequest("TRUNCATE TABLE cache_urls");
        if ($this->isTable("cache_urls_gets"))
            $this->runRequest("TRUNCATE TABLE cache_urls_gets");

        $this->runRequest("SET FOREIGN_KEY_CHECKS = 1")->execute();
    }

    /**
     * Create the cache tables
     */
    protected function createTableURL()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `cache_urls` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `identifier` varchar(255) NOT NULL DEFAULT '',
            `url` varchar(255) NOT NULL DEFAULT '',
            `module` varchar(255) NOT NULL DEFAULT '',
            `controller` varchar(255) NOT NULL DEFAULT '',
            `action` varchar(255) NOT NULL DEFAULT '',
            `isapi` int(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
        );";

        $this->runRequest($sql);

        $this->addColumn("cache_urls", "isapi", "int(1)", 0);


        $sqlg = "CREATE TABLE IF NOT EXISTS `cache_urls_gets` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `url_id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL DEFAULT '',
            `regexp` varchar(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`)
        );";

        $this->runRequest($sqlg);
    }

    /**
     * get the information of a route from it path
     * @param type $path
     * @return type
     */
    public function getURLInfos($path)
    {
        $sql = "SELECT * FROM cache_urls WHERE url=?";
        $urlInfo = $this->runRequest($sql, array($path))->fetch();
        if (!$urlInfo) {
            return null;
        }

        $sqlg = "SELECT * FROM cache_urls_gets WHERE url_id=?";
        $urlInfo["gets"] = $this->runRequest($sqlg, array($urlInfo["id"]))->fetchAll();

        return $urlInfo;
    }

    public function listAll()
    {
        $sql = "SELECT * FROM cache_urls";
        $urlInfo = $this->runRequest($sql)->fetchAll();
        if (!$urlInfo) {
            return array();
        }
        $urls = [];
        foreach ($urlInfo as $url) {
            $sqlg = "SELECT * FROM cache_urls_gets WHERE url_id=?";
            $params = $this->runRequest($sqlg, array($url["id"]))->fetchAll();
            $urlParams = [$url['url'], sprintf('%s/%s/%s', $url['module'], $url['controller'], $url['action'])];
            foreach ($params as $param) {
                $urlParams[] = $param['name'];
            }
            $urls[] = $urlParams;
        }

        return $urls;
    }
}
