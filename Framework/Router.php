<?php

require_once 'Controller.php';
require_once 'Request.php';
require_once 'View.php';
require_once 'FCache.php';
require_once 'Configuration.php';
require_once 'Errors.php';

/**
 * Class that routes the input requests
 * 
 * @author Sylvain Prigent
 */
class Router {

    private $logger;

    protected $modelCache;
    protected $useRouterController; 
    protected $router;

    public function __construct() {
        $this->modelCache = new FCache();
        $this->logger = Configuration::getLogger();
        $this->router = new AltoRouter();
    }

    private function call($target, $args, $request) {
        if(isset($args['id_space'])){
            $_SESSION['id_space'] = $args['id_space'];
        }
        $route_info = explode("/", $target);
        $module = $route_info[0];
        $controller_name = $route_info[1];
        $controller = null;
        $action = $route_info[2];
        try {
            $controller = $this->createControllerImp($module, $controller_name, false, $request);
        } catch (PfmRoutingException $e) {
            $this->logger->warning('no controller found, redirect to homepage', [
                'url' => $request->getParameter('path'),
                'controller' => $controller_name,
                'module' => $module
            ]);
            $controller = $this->createControllerImp('core', 'coretiles', false, $request);
            $action = 'index';
        }
        $this->logger->debug('[router] call', ["controller" => $controller, "action" => $action, "args" => $args]);
        $controller->runAction($module, $action, $args);
        return $module."_".$controller_name."_".$action;
    }

    private function route($request) {
        $modulesNames = Configuration::get("modules");
        $modulesNames = is_array($modulesNames) ? $modulesNames : [$modulesNames];
        foreach ($modulesNames as $moduleName) {
            // get the routing class
            $routingClassUrl = "Modules/" . $moduleName . "/" . ucfirst($moduleName) . "Routing.php";
            if (file_exists($routingClassUrl)) {
                require_once ($routingClassUrl);
                $className = ucfirst($moduleName) . "Routing";
                $routingClass = new $className ();
                if(method_exists($routingClass, "routes")){
                    Configuration::getLogger()->debug('[router] load routes from '.$routingClassUrl);
                    $routingClass->routes($this->router);
                }
            }
        }

        $this->router->map( 'GET', '/ooc/[a:provider]/authorized', 'core/openid/connect', 'ooc' );
        //Configuration::getLogger()->debug('Routes', ['routes' => $this->router->getRoutes()]);
        $match = $this->router->match();
        if(!$match) {
            Configuration::getLogger()->debug('No route match, check old way');
            return null;
        }

        return $this->call($match['target'], $match['params'], $request);
    }

    /**
     * Main method called by the frontal controller
     * Examine a request and run the dedicated action
     */
    public function routerRequest() {
        if(Configuration::get('redis_host') && $_SERVER['REQUEST_URI'] == '/metrics') {
            \Prometheus\Storage\Redis::setDefaultOptions(
                [
                    'host' => Configuration::get('redis_host'),
                    'port' => intval(Configuration::get('redis_host', 6379)),
                    'password' => null,
                    'timeout' => 0.1, // in seconds
                    'read_timeout' => '10', // in seconds
                    'persistent_connections' => false
                ]
            );
            $registry = \Prometheus\CollectorRegistry::getDefault();
            $renderer = new \Prometheus\RenderTextFormat();
            $result = $renderer->render($registry->getMetricFamilySamples());
            header('Content-type: ' . \Prometheus\RenderTextFormat::MIME_TYPE);
            echo $result;
            return;
        }

        $reqStart = microtime(true);
        $reqEnd = $reqStart;
        $reqRoute = "root";
        try {
            // Merge parameters GET and POST
            $params = array();
            if(isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json')  {
                $json = file_get_contents('php://input');
                if(!empty($json)) {
                    $params = json_decode($json, true);
                }
                $params['path'] = $_GET['path'];
            } else {
                $params = array_merge($_GET, $_POST);
            }
            $request = new Request($params);
            if (!$this->install($request)) {
                $reqRoute = $this->route($request);
                if ($reqRoute) {
                    $reqEnd = microtime(true);
                    $this->prometheus($reqStart, $reqEnd, $reqRoute);
                    return;
                }

                $urlInfo = $this->getUrlData($request);
                if(!$urlInfo['pathInfo']) {
                    $this->logger->warning('no route found, redirect to homepage', [
                        'url' => $request->getParameter('path'),
                    ]);
                    $this->call('core/coretiles/index', [], $request);
                    return;
                }
                $controller = $this->createController($urlInfo, $request);
                $action = $urlInfo["pathInfo"]["action"];
                $reqRoute = $urlInfo["pathInfo"]["module"]."_".$urlInfo["pathInfo"]["controller"]."_".$action;
                $args = $this->getArgs($urlInfo);
                if(isset($args['id_space'])){
                    $_SESSION['id_space'] = $args['id_space'];
                }

                $this->logger->debug('[router][old] call', ["controller" => $controller, "action" => $action, "args" => $args]);
                $this->runAction($controller, $urlInfo, $action, $args);
                $reqEnd = microtime(true);
            }
        } catch (Throwable $e) {
            Configuration::getLogger()->error('[router] something went wrong', ['error' => $e->getMessage(), 'line' => $e->getLine(), "file" => $e->getFile(),  'stack' => $e->getTraceAsString()]);
            $reqEnd = microtime(true);
            $this->manageError($e);
        }
        $this->prometheus($reqStart, $reqEnd, $reqRoute);

    }

    private function prometheus($reqStart, $reqEnd, $reqRoute) {
        if(!Configuration::get('redis_host')) {
            return;
        }
        Configuration::getLogger()->info('[prometheus] stat', ['route' => $reqRoute]);
        try {
            \Prometheus\Storage\Redis::setDefaultOptions(
                [
                    'host' => Configuration::get('redis_host'),
                    'port' => intval(Configuration::get('redis_host', 6379)),
                    'password' => null,
                    'timeout' => 0.1, // in seconds
                    'read_timeout' => '10', // in seconds
                    'persistent_connections' => false
                ]
            );
            $registry = \Prometheus\CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter('pfm', 'request_nb', 'quantity', ['url', 'code']);
            $counter->incBy(1, [$reqRoute, http_response_code()]);
            $gauge = $registry->getOrRegisterHistogram('pfm', 'request_time', 'time', ['type', 'url', 'code'], [10, 20, 50, 100, 1000]);
            $gauge->observe(($reqEnd - $reqStart)*1000, [$_SERVER['REQUEST_METHOD'], $reqRoute, http_response_code()]);
        } catch(Exception $e) {
            Configuration::getLogger()->error('[prometheus] error', ['error' => $e]);
        }
    }

    protected function runAction($controller, $urlInfo, $action, $args) {
        if ($urlInfo["pathInfo"]["isapi"]) {
            try {
                $controller->runAction($urlInfo["pathInfo"]["module"], $action, $args);
            } 
            catch (Throwable $ex) {
                echo json_encode(array(
                    'error' => array(
                        'msg' => $ex->getMessage(),
                        'code' => $ex->getCode(),
                    ),
                ));
            }
        } else {
            if ($this->useRouterController) {
                $controller->indexAction($args);
            } else {
                $controller->runAction($urlInfo["pathInfo"]["module"], $action, $args);
            }
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
                throw new PfmDbException("The database is already installed");
            }

            $controller = $this->createControllerImp("core", "coreinstall", 0, $request);
            $controller->runAction("core", "index");
            return true;
        } else if ($path == "caches") {
            $install_modelCache = new FCache();
            $install_modelCache->load();
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
            // throw new Exception("The URL is not valid: unable to find the path");
        }

        $pathData = explode("/", $path);
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
            $fileController = 'Modules/' . strtolower($module) . "/Api/" . $classController . ".php";
        } else {
            $classController = ucfirst(strtolower($controllerName)) . "Controller";
            $module = $moduleName;
            $fileController = 'Modules/' . strtolower($module) . "/Controller/" . $classController . ".php";
        }

        if (file_exists($fileController)) {
            // Instantiate controler
            require ($fileController);
            $controller = new $classController ($request);
            $this->useRouterController = false;
            return $controller;
        } else {
            $rooterController = Configuration::get("routercontroller");
            if($rooterController != ""){
                $rooterControllerArray = explode("::", "$rooterController");
                if(count($rooterControllerArray) == 3){
                    $classController = $rooterControllerArray[2];
                    $fileController = 'Modules/' . strtolower($rooterControllerArray[0]) . "/Controller/" . $rooterControllerArray[2] . ".php";
                    if(file_exists($fileController)){
                        
                        require ($fileController);
                        $controller = new $classController ($request);
                        $this->useRouterController = true;
                        return $controller;
                    }
                }
                else{
                    throw new PfmRoutingException("routercontroller config is not correct. The parameter must be ModuleName::Controller::ControllerName");
                }
            }
            else{
                throw new PfmRoutingException("Unable to find the controller file '$fileController' ");
            }
        }
    }

    /**
     * 
     * @param type $urlInfo
     * @param Request $request
     * @return type
     */
    private function createController($urlInfo, Request $request) {
        return $this->createControllerImp($urlInfo["pathInfo"]["module"], $urlInfo["pathInfo"]["controller"], $urlInfo["pathInfo"]["isapi"], $request);
    }

    /**
     * Manage error (exception)
     *
     * @param Exception $exception
     *        	Thrown exception
     */
    private function manageError(Throwable $exception, $type = '') {

        if(Configuration::get('sentry_dsn', '')) {
            \Sentry\captureException($exception);
        }

        $errCode = 500;
        if($exception instanceof PfmException) {
            $errCode = $exception->getCode();
            if($errCode == 0) {
                $errCode = 500;
            }
        }
        http_response_code($errCode);

        if(isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] == 'application/json')  {
            echo json_encode(array(
                'type' => $type,
                'message' => $exception->getMessage()
            ));
            return;
        }

        $view = new View('error');
        $view->setFile('Modules/error.php');
        $view->generate(array(
            'type' => $type,
            'message' => $exception->getMessage()
        ));
    }

}
