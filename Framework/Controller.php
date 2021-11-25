<?php

require_once 'Configuration.php';
require_once 'Request.php';
require_once 'View.php';
require_once 'Errors.php';

class Navbar {

    /**
     * Get the navbar content
     * @return string
     */
    public function nav() {
        $login = '';
        if(isset($_SESSION["login"])) {
            $login = $_SESSION["login"];
        }
        return $this->buildNavBar($login);
    }

    public function getLanguage() {
        $lang = substr(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'), 0, 2);
        if (isset($_SESSION["user_settings"]["language"])) {
            $lang = $_SESSION["user_settings"]["language"];
        }
        return $lang;
    }

    /**
     * Get the tool menu
     * @return multitype: tool menu content
     */
    public function getMenu() {
        
        $modelMainMenus = new CoreMainMenu();
        $mainMenu = $modelMainMenus->getAll();
        
        $modelMainSubMenus = new CoreMainSubMenu();
        
        for($i = 0 ; $i < count($mainMenu) ; $i++){
            $mainMenu[$i]["items"] = $modelMainSubMenus->getForMenu($mainMenu[$i]["id"]);
        }
        return $mainMenu;
    }
    
    /**
     * Get the admin menu
     * @return multitype: Amdin menu
     */
    public function getAdminMenu() {
        if(!isset($_SESSION["user_status"])) {
            return null;
        }
        $user_status_id = $_SESSION["user_status"];

        $toolAdmin = null;
        if ($user_status_id >= CoreStatus::$ADMIN) {
            $modulesModel = new CoreAdminMenu();
            $toolAdmin = $modulesModel->getAdminMenus();
        }
        return $toolAdmin;
    }

    /**
     * Get the navbar view
     * @param string $login User login
     * @return string: Menu view (html) 
     */
    public function buildNavBar($login) {
        $userName = $login;
        $lang = $this->getLanguage();
        $toolMenu = $this->getMenu();
        $toolAdmin = $this->getAdminMenu();
        

        // get the view menu,fill it, and return the content
        return $this->generateNavfile(
                array('userName' => $userName,
                    'toolMenu' => $toolMenu, 
                    'toolAdmin' => $toolAdmin,
                    'impersonate' => $_SESSION['logged_login'] ?? null,
                    'lang' => $lang));
    }

    /**
     * Internal method to build the navbar into HTML
     * @param  $data navbar content
     * @throws Exception
     * @return string Menu view (html) 
     */
    private function generateNavfile($data) {
        $file = 'Modules/core/View/navbar.php';
        if (file_exists($file)) {
            extract($data);

            ob_start();

            require $file;

            return ob_get_clean();
        } else {
            throw new PfmFileException("unable to find the file: '$file' ", 404);
        }
    }

}


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

    protected $twig;

    public function __construct(Request $request) {
        $this->request = $request;
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/..');
        if(!is_dir('/tmp/pfm')) {
            mkdir('/tmp/pfm');
        }
        if(getenv('PFM_MODE')=='dev') {
            $this->twig = new \Twig\Environment($loader, []);
        } else {
            $this->twig = new \Twig\Environment($loader, [
                'cache' => '/tmp/pfm'
            ]);
        }
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
        Configuration::getLogger()->debug("[controller][runAction]", ["module" => $module, "action" => $action, "args" => $args]);
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
     * Return json encoded data with expected content-type
     */
    protected function api($data = array()) {
        header('Content-Type: application/json');
        if($data) {
            ob_start();
            try {
                echo json_encode($data);
            } catch(Exception $e) {
                Configuration::getLogger()->error('[api] json error', ['error', $e->getMessage()]);
            }
            ob_end_flush();
            flush();
        }
    }

    /**
     * Define the default action
     */
    //public abstract function indexAction();

    /**
     * Generate the vue associated to the curent controller
     * 
     * @param array $dataView Data needed by the view
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
                ob_start();
                // Configuration::getLogger()->debug('[api] response', ['data' => json_encode($dataView['data'])]);
                try {
                    echo json_encode($dataView['data']);
                } catch(Exception $e) {
                    Configuration::getLogger()->error('[api] json error', ['error', $e->getMessage()]);
                }
                ob_end_flush();
                flush();
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
        if(file_exists("Modules/core/View/$controllerView/$actionView.twig")) {
            // TODO add navbar generation
            $dataView["navbar"] = (new Navbar())->nav();
            try {
                echo $this->twig->render("Modules/core/View/$controllerView/$actionView.twig", $dataView);
                return;
            } catch(Throwable $e) {
                Configuration::getLogger()->debug('[view] twig error, using php view', ['err' => $e->getMessage()]);
                $view = new View($actionView, $controllerView, $this->module);
                $view->generate($dataView);
                return;
            }
        }

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
            ob_start();
            echo json_encode($data);
            ob_end_flush();
            flush();
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
