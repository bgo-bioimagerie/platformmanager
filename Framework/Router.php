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

                $this->runAction($controller, $urlInfo, $action, $args);
                //$controller->runAction($urlInfo["pathInfo"]["module"], $action, $args);
            }
        } catch (Exception $e) {
            $this->manageError($e);
        }
    }

    protected function runAction($controller, $urlInfo, $action, $args) {
        if ($urlInfo["pathInfo"]["isapi"]) {
            try {
                $controller->runAction($urlInfo["pathInfo"]["module"], $action, $args);
            } 
            catch (Exception $ex) {
                echo json_encode(array(
                    'error' => array(
                        'msg' => $ex->getMessage(),
                        'code' => $ex->getCode(),
                    ),
                ));
            }
        } else {
            $controller->runAction($urlInfo["pathInfo"]["module"], $action, $args);
        }
    }

    /**
     * Install request 
     * @param type $request
     * @return boolean
     * @throws Exception
     */
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

            $controller = $this->createControllerImp("core", "coreinstall", 0, $request);
            $controller->runAction("core", "index");
            return true;
        } else if ($path == "caches") {
            $modelCache = new FCache();
            $modelCache->load();
            echo "Caches are up to date";
            return true;
        }
        return false;
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    private function getUrlData(Request $request) {

        // get controller name
        $path = "";
        if ($request->isParameterNotEmpty('path')) {
            $path = $request->getParameter('path');
        } else {
            $path = "coretiles";
            //throw new Exception("The URL is not valid: unable to find the path");
        }

        //echo "path = " . $path . "<br/>";
        $pathData = explode("/", $path);
        //echo "path data = ";
        //print_r($pathData);
        //echo "<br/>";
        $pathInfo = $this->modelCache->getURLInfos($pathData[0]);
        return array("pathData" => $pathData, "pathInfo" => $pathInfo);
    }

    /**
     * 
     * @param type $urlInfo
     * @return string
     */
    private function getArgs($urlInfo) {

        $args = $urlInfo["pathInfo"]["gets"];
        $argsValues = array();

        /*
          if (count($args) > count($urlInfo["pathData"]) - 1) {
          throw new Exception("Missing arguments in the URL");
          }
          for ($i = 0; $i < count($args); $i++) {
          $argsValues[$args[$i]["name"]] = $urlInfo["pathData"][$i + 1];
          }
         */
        for ($i = 0; $i < count($args); $i++) {
            if (isset($urlInfo["pathData"][$i + 1])) {
                $argsValues[$args[$i]["name"]] = $urlInfo["pathData"][$i + 1];
            } else {
                $argsValues[$args[$i]["name"]] = "";
            }
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
    private function createControllerImp($moduleName, $controllerName, $isApi, Request $request) {

        if ($isApi) {
            $classController = ucfirst(strtolower($controllerName)) . "Api";
            $module = $moduleName;
            $fileController = 'Modules/' . $module . "/Api/" . $classController . ".php";
        } else {
            $classController = ucfirst(strtolower($controllerName)) . "Controller";
            $module = $moduleName;
            $fileController = 'Modules/' . $module . "/Controller/" . $classController . ".php";
        }

        //echo "controller file = " . $fileController . "<br/>";
        if (file_exists($fileController)) {
            // Instantiate controler
            require ($fileController);
            $controller = new $classController ($request);
            //$controller->setRequest($request);
            return $controller;
        } else {
            throw new Exception("Unable to find the controller file '$fileController' ");
        }
    }

    /**
     * 
     * @param type $urlInfo
     * @param Request $request
     * @return type
     */
    private function createController($urlInfo, Request $request) {
        //print_r($urlInfo);
        return $this->createControllerImp($urlInfo["pathInfo"]["module"], $urlInfo["pathInfo"]["controller"], $urlInfo["pathInfo"]["isapi"], $request);
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
