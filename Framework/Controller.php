<?php

require_once 'Configuration.php';
require_once 'Request.php';
require_once 'View.php';
require_once 'Errors.php';

/**
 * Abstract class defining a controller. 
 * 
 * @author Sylvain Prigent
 */
abstract class Controller {

    /** Action to run */
    protected $action;
    protected $module;

    /** recieved request */
    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Define the input request
     * 
     * @param Request $request Recieved request
     */
    public function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * 
     * @return type The navigator language
     */
    public function getLanguage() {
        $lang = substr(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'), 0, 2);
        if (isset($_SESSION["user_settings"]["language"])) {
            $lang = $_SESSION["user_settings"]["language"];
        }
        return $lang;
    }

    /**
     * Run the action.
     * Call the method with the same name than the action in the curent controller
     * 
     * @throws Exception If the action does not exist in the curent controller
     */
    public function runAction($module, $action, $args = array()) {
        $this->module = strtolower($module);
        $actionName = $action . "Action";
        if (method_exists($this, $actionName)) {
            $this->action = $action;
            //print_r($args);
            call_user_func_array(array($this, $actionName), $args);
            //$this->{$this->action}();
        } else {
            $classController = get_class($this);
            throw new PfmException("Action '$action'Action in not defined in the class '$classController'", 500);
        }
    }

    /**
     * Define the default action
     */
    //public abstract function indexAction();

    /**
     * Generate the vue associated to the curent controller
     * 
     * @param array $dataView Data neededbu the view
     * @param string $action Action associated to the view
     */
    protected function render($dataView = array(), $action = null) {
        // Use the curent action by default
        $actionView = $this->action . "Action";
        if ($action != null) {
            $actionView = $action;
        }
        $classController = get_class($this);
        $controllerView = str_replace("Controller", "", $classController);

        if(isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] == "application/json"){
            header('Content-Type: application/json');
            if(isset($dataView['data'])) {
                echo json_encode($dataView['data']);
            }
            return null;
        }

        if (getenv("PFM_MODE") == "test") {
            return $dataView;
        }

        if(isset($_SESSION['flash'])) {
            $dataView['flash'] = ['msg' => $_SESSION['flash'], 'class' => 'warning'];
            unset($_SESSION['flash']);
            if(isset($_SESSION['flashClass'])) {
                $dataView['flash']['class'] = $_SESSION['flashClass'];
                unset($_SESSION['flashClass']);
            }
        } else {
            $dataView['flash'] = null;
        }
        // Geneate the view
        // echo "controllerView = " . $controllerView . "<br/>";
        //echo "parent = " . basename(__DIR__) . "<br/>";
        $view = new View($actionView, $controllerView, $this->module);
        $view->generate($dataView);
    }

    /**
     * Redirect to a controller and a specific action
     * 
     * @param string $path Path to the controller adn action
     * @param type $args Get arguments
     */
    protected function redirect($path, $args = array(), $data = array()) {
        if(!empty($data) && isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] == "application/json"){
            header('Content-Type: application/json');
            echo json_encode($data);
            return null;
        }
        $rootWeb = Configuration::get("rootWeb", "/");
        foreach ($args as $key => $val) {
            $path .= "?" . $key . "=" . $val;
        }
        if(!headers_sent($filename, $filenum)) {
            header_remove();
        } else {
            Configuration::getLogger()->debug('headers already sent', ['file' => $filename, 'line' => $filenum]);
        }
        header("Location:" . $rootWeb . $path);
    }

    protected function redirectNoRemoveHeader($path, $args = array()){
        $rootWeb = Configuration::get("rootWeb", "/");
        foreach ($args as $key => $val) {
            $path .= "?" . $key . "=" . $val;
        }
        header("Location:" . $rootWeb . $path);
    }

}
