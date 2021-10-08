<?php

require_once 'Framework/Form.php';
require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';

/**
 * Edit the application settings
 * 
 * @author sprigent
 *
 */
class CoresettingsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->checkAuthorization(CoreStatus::$USER);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {
        // get the available modules settings
        $modules = Configuration::get("modules");
        $modules = is_array($modules) ? $modules : [$modules];
        $modulesControllers = array();
        $i = -1;
        foreach ($modules as $module) {
            $controllerName = $module . "usersettings";
            $controllerName = ucfirst(strtolower($controllerName));

            $fileController = 'Modules/' . $module . "/Controller/" . $controllerName . "Controller.php";
            if (file_exists($fileController)) {
                $i++;
                $modulesControllers[$i]["module"] = $module;
                $modulesControllers[$i]["controller"] = $controllerName;
            }
        }
        $lang = $this->getLanguage();
        return $this->render(array(
            'lang' => $lang,
            'modulesControllers' => $modulesControllers,
        ));
    }
}
