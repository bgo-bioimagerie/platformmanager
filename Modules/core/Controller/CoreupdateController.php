<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';

/**
 * 
 * @author sprigent
 * Manage the modules: starting page to install and config each module	
 */
class CoreupdateController extends CoresecureController {

    public function __construct() {
        parent::__construct();
        $this->checkAuthorization(CoreStatus::$ADMIN);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {

        $lang = $this->getLanguage();

        $form = new Form($this->request, "coreupdateform");
        $form->setTitle(CoreTranslator::Update($lang));
        $form->addComment(CoreTranslator::UpdateComment($lang));
        $form->setValidationButton(CoreTranslator::Update($lang), 'coreupdate');

        if ($form->check()) {
            $_SESSION['message'] = $this->update();
            $this->redirect('coreupdate');
        }

        $this->render(array('formHtml' => $form->getHtml($lang)
        ));
    }

    private function update() {

        try {
            $modules = Configuration::get('modules');
            for ($i = 0; $i < count($modules); ++$i) {

                $moduleName = ucfirst(strtolower($modules[$i]));
                $installFile = "Modules/" . $modules[$i] . "/Model/" . $moduleName . "Install.php";
                if (file_exists($installFile)) {

                    require_once $installFile;
                    $className = $moduleName . "Install";
                    $object = new $className();
                    $object->createDatabase();
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return "Success: update done ";
    }

}
