<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';

/**
 * 
 * @author sprigent
 * Manage the modules: starting page to install and config each module	
 */
class CoremodulesmanagerController extends CoresecureController {

    public function __construct() {
        parent::__construct();
        $this->checkAuthorization(CoreStatus::$SUPERADMIN);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {

        $lang = $this->getLanguage();
        // get the modules list
        $modules = Configuration::get('modules');
        $mods = array();
        $count = -1;
        for ($i = 0; $i < count($modules); ++$i) {

            $moduleName = ucfirst(strtolower($modules[$i]));
            $abstractMethod = $moduleName . "ConfigAbstract";
            //echo "abstractMethod = " . $abstractMethod . "<br/>";
            //echo "abstractMethod = " . $abstractMethod . "<br/>";
            $configFile = "Modules/" . $modules[$i] . "/Controller/" . $moduleName . "configController.php";
            if (file_exists($configFile)) {
                $count++;
                // name
                $mods[$count]['name'] = $modules[$i];
                if ($modules[$i] != "core"){
                    require_once "Modules/" . $modules[$i] . "/Model/" . $moduleName . "Translator.php" ;
                }
                // get abstract html text
                $mods[$count]['abstract'] = forward_static_call(array($moduleName . "Translator", $abstractMethod), $lang);
                // construct action
                $action = $modules[$i] . "config";
                $mods[$count]['action'] = $action;
                $mods[$count]['id'] = $i;
            }
        }

        $headers = array("name" => CoreTranslator::Name($lang),
            "abstract" => CoreTranslator::Description($lang));

        $tableView = new TableView();
        $tableView->setTitle(CoreTranslator::Modules_configuration($lang));
        $tableView->addLineEditButton("coremodulesmanagerconfig");
        $tableHtml = $tableView->view($mods, $headers);

        $this->render(array(
            "tableHtml" => $tableHtml
        ));
    }

    public function configAction($id){
        $modules = Configuration::get('modules');
        $path = $modules[$id] . "config";
        $this->redirect($path);
    }
}
