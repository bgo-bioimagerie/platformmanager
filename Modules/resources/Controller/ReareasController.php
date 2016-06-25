<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/ecosystem/Model/EcSite.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ReareasController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->model = new ReArea();
        $this->checkAuthorizationMenu("resources");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();
        
        $modelSite = new EcSite();
        $sites = $modelSite->getUserAdminSites($_SESSION["id_user"]);
        
        
        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Areas($lang));
        $table->addLineEditButton("reareasedit");
        $table->addDeleteButton("reareasdelete");
        
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );
        
        if (count($sites) > 1){
            $headers["site"] = EcosystemTranslator::Site($lang);
        }
        
        $categories = $this->model->getAll();
            
        $tableHtml = $table->view($categories, $headers);
        
        $this->render(array("lang" => $lang, "htmlTable" => $tableHtml));
    }
    
      /**
     * Edit form
     */
    public function editAction($id) {

        // get belonging info
        $site = array("id" => 0, "name" => "", "id_site" => 1);
        if ($id > 0) {
            $site = $this->model->get($id);
        }

        // lang
        $lang = $this->getLanguage();
        
        $modelSite = new EcSite();
        $sites = $modelSite->getUserAdminSites($_SESSION["id_user"]);
        $allSites = $modelSite->getAll("name");
        $choices = array(); $choicesid = array();
        foreach($allSites as $s){
            $choices[] = $s["name"];
            $choicesid[] = $s["id"];
        }

        // form
        // build the form
        $form = new Form($this->request, "reareasedit");
        $form->setTitle(ResourcesTranslator::Edit_Area($lang));
        $form->addHidden("id", $site["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $site["name"]);
        
        if(count($sites) == 1){
            $form->addHidden("id_site", $sites[0]["id"]);
        }
        else if (count($sites) > 1){
            $form->addSelect("id_site", EcosystemTranslator::Site($lang), $choices, $choicesid, $site["id_site"]);
        }
        else{
            throw new Exception(EcosystemTranslator::NeedToBeSiteManager($lang));
        }

        $form->setValidationButton(CoreTranslator::Ok($lang), "reareasedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "reareas");

        if ($form->check()) {
            // run the database query
            $this->model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("id_site"));
            $this->redirect("reareas");
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }
    
    public function deleteAction($id){
        $this->model->delete($id);
        $this->redirect("reareas");
    }

}
