<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';

/**
 * @deprecated
 * @author sprigent
 * Manage the modules: starting page to install and config each module
 */
class CoreupdateController extends Controller
{
    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction()
    {
        $lang = $this->getLanguage();

        $updateInfo = $this->updateAction(1);

        return $this->render(array(
            'lang' => $lang,
            'updateInfo' => $updateInfo
        ));
    }

    public function updateAction($fromButton = 0)
    {
        try {
            $modulesInstalled = '';
            $first = true;
            $modules = Configuration::get('modules');
            for ($i = 0; $i < count($modules); ++$i) {
                $moduleName = ucfirst(strtolower($modules[$i]));
                $installFile = "Modules/" . $modules[$i] . "/Model/" . $moduleName . "Install.php";
                if (file_exists($installFile)) {
                    if (!$first) {
                        $modulesInstalled .= ", ";
                    } else {
                        $first = false;
                    }
                    $modulesInstalled .= $modules[$i];
                    require_once $installFile;
                    $className = $moduleName . "Install";
                    $object = new $className();
                    $object->createDatabase();
                }
            }
        } catch (Exception $e) {
            if ($fromButton == 0) {
                echo $e->getMessage();
            } else {
                return array("status" => "error", "message" =>  $e->getMessage());
            }
        }


        if ($fromButton == 0) {
            echo "Success: update done for modules: " . $modulesInstalled;
        } else {
            return array( "status" => "success", "message" => "Update done for modules: " . $modulesInstalled );
        }
    }
}
