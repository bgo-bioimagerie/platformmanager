<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/ecosystem/Model/EcSite.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class RecategoriesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->categoryModel = new ReCategory();
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
        $table->setTitle(ResourcesTranslator::Categories($lang));
        
        $table->addLineEditButton("recategoriesedit");
        $table->addDeleteButton("recategoriesdelete");
        
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );
        
        if (count($sites) > 1){
            $headers["site"] = EcosystemTranslator::Site($lang);
        }
        $categories = $this->categoryModel->getAll();
        $tableHtml = $table->view($categories, $headers);
        $this->render(array("lang" => $lang, "tableHtml" => $tableHtml));
    }
    
      /**
     * Edit form
     */
    public function editAction($id) {

        // get belonging info
        $data = array("id" => 0, "name" => "", "id_site" => 1);
        if ($id > 0) {
            $data = $this->categoryModel->get($id);
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
        $form = new Form($this->request, "recategoriesedit");
        $form->setTitle(ResourcesTranslator::Edit_Category($lang));
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        if(count($sites) == 1){
            $form->addHidden("id_site", $sites[0]["id"]);
        }
        else if (count($sites) > 1){
            $form->addSelect("id_site", EcosystemTranslator::Site($lang), $choices, $choicesid, $data["id_site"]);
        }
        else{
            throw new Exception(EcosystemTranslator::NeedToBeSiteManager($lang));
        }
        $form->setValidationButton(CoreTranslator::Ok($lang), "recategoriesedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "recategories");

        if ($form->check()) {
            // run the database query
            $model = new ReCategory();
            $model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("id_site"));
            $this->redirect("recategories");
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
        $this->categoryModel->delete($id);
        $this->redirect("recategories");
    }
}
