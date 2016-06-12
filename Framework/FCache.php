<?php

require_once 'Framework/Model.php';

/**
 * Cache the url informations to speed the routing
 *
 * @author Sylvain Prigent
 */
class FCache extends Model {

    /**
     * Load the urls into the cach table
     * Call this function to generate the cache
     */
    public function load() {

        // URLS
        $this->createTableURL();
        $this->loadUrls();
    }

    /**
     * Implement the URL loading
     */
    public function loadUrls() {

        // get the modules list
        $modulesNames = Configuration::get("modules");

        // load each modules
        foreach ($modulesNames as $moduleName) {

            // get the routing class
            $routingClassUrl = "Modules/" . $moduleName . "/" . ucfirst($moduleName) . "Routing.php";
            if (file_exists($routingClassUrl)) {

                $this->addRoutsToDatabase($moduleName, $routingClassUrl);
            } else {
                throw new Exception("The module '$moduleName' has a not valid routing file");
            }
        }
    }

    /**
     * Get all the route information to add it to the database
     * @param type $moduleName Name of the module
     * @param type $routingClassUrl url of the controller
     */
    protected function addRoutsToDatabase($moduleName, $routingClassUrl) {
        require ($routingClassUrl);
        $className = ucfirst($moduleName) . "Routing";
        //echo "rooting class name " . $className . "<br/>";
        $routingClass = new $className ();
        $routingClass->listRouts();
        for ($r = 0; $r < $routingClass->count(); $r++) {
            $identifier = $routingClass->getIdentifier($r);
            $url = $routingClass->getUrl($r);
            $controller = $routingClass->getController($r);
            $actions = $routingClass->getAction($r);
            $gets = $routingClass->getGet($r);
            $getsRegexp = $routingClass->getGetRegexp($r);

            $this->setCacheUrl($identifier, $url, $moduleName, $controller, $actions, $gets, $getsRegexp);
        }
    }

    /**
     * call request methods to add a route to the database
     * @param type $identifier
     * @param type $url
     * @param type $module
     * @param type $controller
     * @param type $actions
     * @param type $gets
     * @param type $getsRegexp
     */
    protected function setCacheUrl($identifier, $url, $module, $controller, $actions, $gets, $getsRegexp) {

        // insert the urls
        $id = $this->setCacheUrlDB($identifier, $url, $module, $controller, $actions);

        // instert the gets
        for ($g = 0; $g < count($gets); $g++) {
            $this->setCacheUrlGetDB($id, $gets[$g], $getsRegexp[$g]);
        }
    }

    /**
     * Request to add a route to the database
     * @param type $identifier
     * @param type $url
     * @param type $module
     * @param type $controller
     * @param type $action
     * @return type
     */
    protected function setCacheUrlDB($identifier, $url, $module, $controller, $action) {
        
        //echo "identifier = " . $identifier . "<br/>";
        
        $id = $this->getChacheUrlID($identifier);
        //echo 'id = ' . $id . "<br/>";
        if ($id > 0) {
            //echo "update cache_urls begin <br/>";
            $sql = "UPDATE cache_urls SET identifier=?, url=?, module=?, controller=?, action=? WHERE id=?";
            $this->runRequest($sql, array($identifier, $url, $module, $controller, $action, $id));
            //echo "update cache_urls end <br/>";
        } else {
            //echo "insert cache_urls begin <br/>";
            $sql = "INSERT INTO cache_urls (identifier, url, module, controller, action) VALUES(?,?,?,?,?) ";
            $this->runRequest($sql, array($identifier, $url, $module, $controller, $action));
            $id = $this->getDatabase()->lastInsertId();
            //echo "insert cache_urls end <br/>";
        }
        return $id;
    }

    /**
     * Request to add a route get parameters to the database
     * @param type $id_url
     * @param type $name
     * @param type $regexp
     * @return type
     */
    protected function setCacheUrlGetDB($id_url, $name, $regexp) {

        //echo "name = " . $name; echo "<br/>";
        //echo "regexp = " . $regexp; echo "<br/>";
        //echo "id_url = " . $id_url; echo "<br/>";
        
        $id = $this->getChacheUrlGetID($id_url, $name);
        //echo "id = "; print_r($id); echo "<br/>";
        if ($id > 0) {
            //echo "UPDATE cache_urls_gets begin <br/>";
            $sql = "UPDATE cache_urls_gets SET `url_id`=?, `name`=?, `regexp`=? WHERE id=?";
            $this->runRequest($sql, array($id_url, $name, $regexp, $id));
            //echo "UPDATE cache_urls_gets end <br/>";
        } else {
            //echo "INSERT cache_urls_gets begin <br/>";
            $sql = "INSERT INTO cache_urls_gets (`url_id`, `name`, `regexp`) VALUES(?,?,?) ";
            $this->runRequest($sql, array($id_url, $name, $regexp));
            $id = $this->getDatabase()->lastInsertId();
            //echo "INSERT cache_urls_gets end <br/>";
        }
        return $id;
    }

    /**
     * get a get parameter cache route id
     * @param type $id_url
     * @param type $name
     * @return boolean
     */
    protected function getChacheUrlGetID($id_url, $name) {
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
     * @param type $identifier
     * @return boolean
     */
    protected function getChacheUrlID($identifier) {
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
    protected function freeTableURL() {

        //if ($this->isTable("cache_urls")){
        $sql = "TRUNCATE TABLE cache_urls";
        $this->runRequest($sql);
        //}
        //if ($this->isTable("cache_urls_gets")){
        $sqlg = "TRUNCATE TABLE cache_urls_gets";
        $this->runRequest($sqlg);
        //}
    }

    /**
     * Create the cache tables
     */
    protected function createTableURL() {
        $sql = "CREATE TABLE IF NOT EXISTS `cache_urls` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`identifier` varchar(255) NOT NULL DEFAULT '',
                `url` varchar(255) NOT NULL DEFAULT '',
                `module` varchar(255) NOT NULL DEFAULT '',
                `controller` varchar(255) NOT NULL DEFAULT '',
                `action` varchar(255) NOT NULL DEFAULT '',
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

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
    public function getURLInfos($path) {
        $sql = "SELECT * FROM cache_urls WHERE url=?";
        $urlInfo = $this->runRequest($sql, array($path))->fetch();

        $sqlg = "SELECT * FROM cache_urls_gets WHERE url_id=?";
        $urlInfo["gets"] = $this->runRequest($sqlg, array($urlInfo["id"]))->fetchAll();

        return $urlInfo;
    }

}
