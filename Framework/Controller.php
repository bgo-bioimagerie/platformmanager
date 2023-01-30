<?php

require_once 'Configuration.php';
require_once 'Request.php';
require_once 'View.php';
require_once 'Errors.php';
require_once 'Constants.php';
require_once 'Modules/core/Model/CoreInstall.php';

require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreMainMenu.php';
require_once 'Modules/core/Model/CoreAdminMenu.php';
require_once 'Modules/core/Model/CoreConfig.php';

use DebugBar\StandardDebugBar;
use DebugBar\DataCollector\PDO\PDOCollector;

// Default navbar
class Navbar
{
    private string $login = '';

    /**
     * Get the navbar content
     * @return string
     */
    public function __construct(public ?string $lang)
    {
        $this->lang = $lang;
        if (isset($_SESSION["login"])) {
            $this->login = $_SESSION["login"];
        }
    }

    public function get(): string
    {
        $userName = $this->login;
        $toolMenu = $this->getMenu();
        $toolAdmin = $this->getAdminMenu();

        // get the view menu,fill it, and return the content
        return $this->generateNavfile(
            array('userName' => $userName,
                'toolMenu' => $toolMenu,
                'toolAdmin' => $toolAdmin,
                'impersonate' => $_SESSION['logged_login'] ?? null,
                "theme" => isset($_SESSION['theme']) ? $_SESSION['theme'] : null,
                'lang' => $this->lang)
        );
    }

    /**
     * Get the tool menu
     * @return multitype: tool menu content
     */
    public function getMenu()
    {
        $modelMainMenus = new CoreMainMenu();
        return $modelMainMenus->getAll();
    }

    /**
     * Get the admin menu
     * @return multitype: Amdin menu
     */
    public function getAdminMenu()
    {
        if (!isset($_SESSION["user_status"])) {
            return null;
        }
        $userStatusId = $_SESSION["user_status"];

        $toolAdmin = null;
        if ($userStatusId >= CoreStatus::$ADMIN) {
            $modulesModel = new CoreAdminMenu();
            $toolAdmin = $modulesModel->getAdminMenus();
        }
        return $toolAdmin;
    }

    /**
     * Internal method to build the navbar into HTML
     * @param  $data navbar content
     * @throws Exception
     * @return string Menu view (html)
     */
    private function generateNavfile($data)
    {
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
abstract class Controller
{
    /** Action to run */
    protected $action;
    protected $module;
    protected $args;

    /** received request */
    protected $request;

    protected $twig;

    protected ?array $currentSpace = null;
    protected int $role = -1;
    protected ?string $maintenance = null;

    public function args()
    {
        return $this->args;
    }

    public function setArgs($args)
    {
        $this->args = $args;
    }

    public function __construct(Request $request, ?array $space=null)
    {
        $this->request = $request;
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/..');
        $baseTmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR. 'pfm';
        if (!is_dir($baseTmpDir)) {
            mkdir($baseTmpDir, 0750, true);
        }
        if (getenv('PFM_MODE')=='dev') {
            $this->twig = new \Twig\Environment($loader, []);
        } else {
            $this->twig = new \Twig\Environment($loader, [
                'cache' => $baseTmpDir
            ]);
        }

        if ($request->getParameterNoException('theme')) {
            $theme = $request->getParameterNoException('theme');
            if ($theme == 'switch') {
                if (!isset($_SESSION['theme'])) {
                    $theme = 'dark';
                } else {
                    switch ($_SESSION['theme']) {
                        case 'dark':
                            $theme = 'light';
                            break;
                        case 'light':
                            $theme = 'dark';
                            break;
                        default:
                            $theme = 'light';
                            break;
                    }
                }
            }
            $_SESSION['theme'] = $theme;
        }

        $this->currentSpace = $space;
        if ($space && $space['id'] && isset($_SESSION['id_user']) && $_SESSION['id_user'] > 0) {
            $m = new CoreSpace();
            $role = $m->getUserSpaceRole($space['id'], $_SESSION['id_user']);
            $this->role = $role;
            if (Configuration::get('sentry_dsn', '')) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($space, $role): void {
                    $scope->setUser([
                        'id' => $_SESSION["id_user"],
                        'username' => $_SESSION["login"] ?? ''
                    ]);
                    $scope->setTag('pfm.space', $space['id']);
                    $scope->setTag('pfm.role', $role);
                });
            }

        }

        $ccm = new CoreConfig();
        $maintenance = $ccm->getParam("is_maintenance", false);
        if ($maintenance) {
            $this->maintenance = $ccm->getParam("maintenance_message", "Site maintenance");
        }
    }

        /**
     *
     * @param int $id_space
     * @return string
     */
    public function mainMenu()
    {
        $space = $this->currentSpace;
        if ($space === null) {
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

    public function sideMenu()
    {
        return null;
    }

    public function spaceMenu()
    {
        return null;
    }

    public function spaceExtraMenus()
    {
        return [];
    }

    /**
     * Define the input request
     *
     * @param Request $request Recieved request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     *
     * @return string The navigator language
     */
    public function getLanguage()
    {
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
    public function runAction($module, $action, $args = array())
    {
        Configuration::getLogger()->debug("[controller][runAction]", ["module" => $module, "action" => $action, "args" => $args]);
        $this->args = $args;
        $this->module = strtolower($module);
        $actionName = $action . "Action";
        if (method_exists($this, $actionName)) {
            $this->action = $action;
            return call_user_func_array(array($this, $actionName), $args);
        } else {
            $classController = get_class($this);
            throw new PfmException("Action '$action'Action not defined in the class '$classController'", 500);
        }
    }

    /**
     * Return json encoded data with expected content-type
     */
    protected function api($data = array())
    {
        header('Content-Type: application/json');
        if ($data) {
            ob_start();
            try {
                echo json_encode($data);
            } catch (Exception $e) {
                Configuration::getLogger()->error('[api] json error', ['error', $e->getMessage()]);
            }
            ob_end_flush();
            flush();
        }
    }

    /**
     * Generate the vue associated to the curent controller
     *
     * @param array $dataView Data needed by the view
     * @param string $action Action associated to the view
     */
    protected function render($dataView = array(), $action = null)
    {
        // Use the curent action by default
        $actionView = $this->action . "Action";
        if ($action != null) {
            $actionView = $action;
        }
        $classController = get_class($this);
        $controllerView = str_replace("Controller", "", $classController);

        $isJson = false;
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accept = explode(',', $_SERVER['HTTP_ACCEPT']);
            foreach ($accept as $a) {
                if ($a == "application/json") {
                    $isJson = true;
                    break;
                }
            }
        }
        if ($isJson) {
            header('Content-Type: application/json');
            if (isset($dataView['data'])) {
                ob_start();
                try {
                    echo json_encode($dataView['data']);
                } catch (Exception $e) {
                    Configuration::getLogger()->error('[api] json error', ['error', $e->getMessage()]);
                }
                ob_end_flush();
            }
            return null;
        }


        if (isset($_SESSION['flash'])) {
            $dataView['flash'] = ['msg' => $_SESSION['flash'], 'class' => 'warning'];
            unset($_SESSION['flash']);
            if (isset($_SESSION['flashClass'])) {
                $dataView['flash']['class'] = $_SESSION['flashClass'];
                unset($_SESSION['flashClass']);
            }
        } else {
            $dataView['flash'] = null;
        }




        // Generate the view
        $dataView["currentSpace"] = $this->currentSpace;
        $dataView["context"] = [
            "id_user" => isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0,
            "mainMenu" => $this->mainMenu(),
            "sideMenu" => $this->sideMenu(),
            "spaceMenu" => $this->spaceMenu(),
            "extraSpaceMenus" => $this->spaceExtraMenus(),
            "rootWeb" => Configuration::get("rootWeb", "/"),
            "lang" => $this->getLanguage(),
            "currentSpace" => $this->currentSpace,  // current space if any
            "role" => $this->role,   // user role in space if any
            "maintenance" => $this->maintenance,
            "theme" => isset($_SESSION['theme']) ? $_SESSION['theme'] : null,
            "dev" => (getenv('PFM_MODE')=='dev')
        ];
        if ($dataView["context"]["dev"]) {
            CoreInstall::getDatabase();
            $debugbar = new StandardDebugBar();
            $debugbarRenderer = $debugbar->getJavascriptRenderer();
            $debugbar->addCollector(new DebugBar\DataCollector\PDO\PDOCollector(CoreInstall::getDatabase()));
            $dataView["context"]["_debugbarRenderer"] = $debugbarRenderer;
        }

        if (getenv("PFM_MODE") == "test") {
            // Need to know module name and action
            if (getenv('PFM_TEST_VIEW') === '1') { // do not test views
                //ob_start();
                if (file_exists("Modules/".$this->module."/View/$controllerView/$actionView.twig")) {
                    $nav = new Navbar($this->getLanguage());
                    $dataView["navbar"] = $nav->get();
                    try {
                        ob_start();
                        echo $this->twig->render("Modules/".$this->module."/View/$controllerView/$actionView.twig", $dataView);
                    } catch (Throwable $e) {
                        Configuration::getLogger()->debug('[view] twig error, using php view', ['err' => $e->getMessage()]);
                        $view = new View($actionView, $controllerView, $this->module);
                        $view->generate($dataView);
                    }
                } else {
                    $view = new View($actionView, $controllerView, $this->module);
                    $view->generate($dataView);
                }
                ob_end_clean();
            }
            if (isset($dataView['data'])) {
                return $dataView['data'];
            }
            return null;
        }

        if (file_exists("Modules/".$this->module."/View/$controllerView/$actionView.twig")) {
            $nav = new Navbar($this->getLanguage());
            $dataView["navbar"] = $nav->get();
            try {
                ob_start();
                echo $this->twig->render("Modules/".$this->module."/View/$controllerView/$actionView.twig", $dataView);
            } catch (Throwable $e) {
                Configuration::getLogger()->debug('[view] twig error, using php view', ['err' => $e->getMessage()]);
                $view = new View($actionView, $controllerView, $this->module);
                $view->generate($dataView);
            }
            ob_end_flush();
            return null;
        }
        $view = new View($actionView, $controllerView, $this->module);
        $view->generate($dataView);
        ob_end_flush();
        return null;
    }

    /**
     * Redirect to a controller and a specific action
     *
     * @param string $path Path to the controller adn action
     * @param array $args Get arguments
     */
    protected function redirect($path, $args = array(), $data = array())
    {
        if (getenv("PFM_MODE") == "test") {
            return $data;
        }

        if (!empty($data) && isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] == "application/json") {
            header('Content-Type: application/json');
            ob_start();
            echo json_encode($data);
            ob_end_flush();
            flush();
            return null;
        }
        $rootWeb = Configuration::get("rootWeb", "/");
        if ($args) {
            $path .= "?";
            $pathElements = [];
            foreach ($args as $key => $val) {
                $pathElements[] = $key . "=" . $val;
            }
            $path .= implode('&', $pathElements);
        }
        if (!headers_sent($filename, $filenum)) {
            header_remove();
        } else {
            Configuration::getLogger()->debug('headers already sent', ['file' => $filename, 'line' => $filenum]);
        }
        $newUrl = $rootWeb . $path;
        $newUrl = str_replace('//', '/', $newUrl);
        header("Location:" . $newUrl);
    }

    protected function redirectNoRemoveHeader($path, $args = array())
    {
        $rootWeb = Configuration::get("rootWeb", "/");
        foreach ($args as $key => $val) {
            $path .= "?" . $key . "=" . $val;
        }
        $newUrl = $rootWeb . $path;
        $newUrl = str_replace('//', '/', $newUrl);
        header("Location:" . $newUrl);
    }
}
