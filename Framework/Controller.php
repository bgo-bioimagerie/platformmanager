<?php

require_once 'Configuration.php';
require_once 'Request.php';
require_once 'View.php';
require_once 'Errors.php';
require_once 'Constants.php';

require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreTranslator.php';

/**
 * Abstract class defining a controller. 
 * 
 * @author Sylvain Prigent
 */
abstract class Controller {

    /** Action to run */
    protected $action;
    protected $module;
    protected $args;

    /** recieved request */
    protected $request;

    protected $twig;

    protected ?array $currentSpace = null;
    protected int $role = -1;

    public function args() {
        return $this->args;
    }

    public function setArgs($args) {
        $this->args = $args;
    }

    public function __construct(Request $request, ?array $space=null) {
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

        $this->currentSpace = $space;
        if($space && $space['id'] && isset($_SESSION['id_user']) && $_SESSION['id_user'] > 0) {
            $m = new CoreSpace();
            $this->role = $m->getUserSpaceRole($space['id'], $_SESSION['id_user']);
        }
    }

        /**
     * 
     * @param int $id_space
     * @return string
     */
    public function mainMenu() {      
        //$m = new CoreSpace();
        //$space = $m->getSpace($id_space);
        $space = $this->currentSpace;
        if($space === null) {
            return '';
        }


        $spaceColor = Constants::COLOR_WHITE;
        if ($space["color"] != "") {
            $spaceColor = $space["color"];
        }
        $spaceTxtColor = Constants::COLOR_BLACK;
        if ($space['txtcolor'] != "") {
            $spaceTxtColor = $space["txtcolor"];
        }

        $dataView = [
            'id' => $space['id'],
            'name' => $space['name'],
            'color' => $spaceColor,
            'txtcolor' => $spaceTxtColor,
            'extraSpaceMenus' => $this->spaceExtraMenus()
        ];

        return $this->twig->render("Modules/core/View/Corespace/navbar.twig", $dataView);
    }

    public function sideMenu() {
        return null;
    }

    public function spaceMenu() {
        return null;
    }

    public function spaceExtraMenus() {
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        if($this->currentSpace && $modelSpace->getSpaceMenusRole($this->currentSpace['id'], "helpdesk")) {
            return [['name' => CoreTranslator::ReportIssue($lang), 'url' => '/helpdesk/'.$this->currentSpace['id'].'/report?url='.urlencode($_SERVER['REQUEST_URI']), 'class' => 'btn-danger']];
        }
        return [];
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
     * @return string The navigator language
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
        $this->args = $args;
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
            if(isset($dataView['data'])) {
                return $dataView['data'];
            }
            return null;
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

       
        // Generate the view
        $dataView["currentSpace"] = $this->currentSpace;
        $dataView["context"] = [
            "mainMenu" => $this->mainMenu(),
            "sideMenu" => $this->sideMenu(),
            "spaceMenu" => $this->spaceMenu(),
            "extraSpaceMenus" => $this->spaceExtraMenus(),
            "rootWeb" => Configuration::get("rootWeb", "/"),
            "currentSpace" => $this->currentSpace,  // current space if any
            "role" => $this->role   // user role in space if any
        ];
        if(file_exists("Modules/core/View/$controllerView/$actionView.twig")) {
            // TODO add navbar generation
            require_once 'Modules/core/Controller/CorenavbarController.php';
            $navController = new CorenavbarController($this->request);
            $dataView["navbar"] = $navController->navbar();
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
        if (getenv("PFM_MODE") == "test") {
            return $data;
        }

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
        $newUrl = $rootWeb . $path;
        $newUrl = str_replace('//', '/', $newUrl);
        header("Location:" . $newUrl);
    }

    protected function redirectNoRemoveHeader($path, $args = array()){
        $rootWeb = Configuration::get("rootWeb", "/");
        foreach ($args as $key => $val) {
            $path .= "?" . $key . "=" . $val;
        }
        $newUrl = $rootWeb . $path;
        $newUrl = str_replace('//', '/', $newUrl);
        header("Location:" . $newUrl);
        // header("Location:" . $rootWeb . $path);
    }

}
