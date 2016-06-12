<?php

require_once 'Controller.php';
require_once 'Request.php';
require_once 'View.php';
require_once 'FCache.php';

/**
 * Class that rout the input requests
 * 
 * @author Sylvain Prigent
 */
class router {

    protected $modelCache;

    public function __construct() {
        $this->modelCache = new FCache();
    }

    /**
     * Main method called by the frontal controller
     * Examine a request and run the dedicated action
     */
    public function routerRequest() {

        try {
            // Merge parameters GET and POST
            $request = new Request(array_merge($_GET, $_POST));

            if (!$this->install($request)) {
                $urlInfo = $this->getUrlData($request);

                //print_r($urlInfo);
                $controller = $this->createController($urlInfo, $request);
                $action = $urlInfo["pathInfo"]["action"];
                $args = $this->getArgs($urlInfo);
                //echo "args = "; print_r($args); echo "<br/>";

                $controller->runAction($urlInfo["pathInfo"]["module"], $action, $args);
            }
        } catch (Exception $e) {
            $this->manageError($e);
        }
    }

    private function install($request) {
        $path = "";
        if ($request->isParameterNotEmpty('path')) {
            $path = $request->getParameter('path');
        }

        if ($path == "install") {

            $dsn = Configuration::get('dsn', '');
            if ($dsn != '') {
                throw new Exception("The database is already installed");
            }

            $controller = $this->createControllerImp("core", "coreinstall", $request);
            $controller->runAction("core", "index");
            return true;
        }
        return false;
    }

    private function getUrlData(Request $request) {

        // get controller name
        $path = "";
        if ($request->isParameterNotEmpty('path')) {
            $path = $request->getParameter('path');
        } else {
            throw new Exception("The URL is not valid: unable to find the path");
        }

        //echo "path = " . $path . "<br/>";
        $pathData = explode("/", $path);
        //echo "path data = "; print_r($pathData); echo "<br/>";
        $pathInfo = $this->modelCache->getURLInfos($pathData[0]);
        return array("pathData" => $pathData, "pathInfo" => $pathInfo);
    }

    private function getArgs($urlInfo) {

        $args = $urlInfo["pathInfo"]["gets"];
        $argsValues = array();

        if (count($args) > count($urlInfo["pathData"]) - 1) {
            throw new Exception("Missing arguments in the URL");
        }
        for ($i = 0; $i < count($args); $i++) {
            $argsValues[$args[$i]["name"]] = $urlInfo["pathData"][$i + 1];
        }

        return $argsValues;
    }

    /**
     * Instantiate the controller dedicated to the request
     *
     * @param Request $request
     *        	Input Request
     * @return Instance of a controller
     * @throws Exception If the controller cannot be instanciate
     */
    private function createControllerImp($moduleName, $controllerName, Request $request) {

        $classController = ucfirst(strtolower($controllerName)) . "Controller";
        $module = $moduleName;
        $fileController = 'Modules/' . $module . "/Controller/" . $classController . ".php";
        //echo "controller file = " . $fileController . "<br/>";
        if (file_exists($fileController)) {
            // Instantiate controler
            require ($fileController);
            $controller = new $classController ();
            $controller->setRequest($request);
            return $controller;
        } else {
            throw new Exception("Unable to find the controller file '$fileController' ");
        }
    }

    private function createController($urlInfo, Request $request) {
        //print_r($urlInfo);
        return $this->createControllerImp($urlInfo["pathInfo"]["module"], $urlInfo["pathInfo"]["controller"], $request);
    }

    /**
     * Manage error (exception)
     *
     * @param Exception $exception
     *        	Thrown exception
     */
    private function manageError(Exception $exception, $type = '') {

        $view = new View('error');
        $view->setFile('Modules/error.php');
        $view->generate(array(
            'type' => $type,
            'message' => $exception->getMessage()
        ));
    }

}
