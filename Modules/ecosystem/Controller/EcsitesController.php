<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/ecosystem/Model/EcSite.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * Manage the units (each user belongs to an unit)
 * 
 * @author sprigent
 *
 */
class EcsitesController extends CoresecureController {

    /**
     * User model object
     */
    private $siteModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->siteModel = new EcSite ();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {
        $lang = $this->getLanguage();

        // get the user list
        $unitsArray = $this->siteModel->getAll("name");

        $table = new TableView();
        $table->addLineEditButton("ecsitesedit");
        $table->addDeleteButton("ecsitesdelete");
        $table->addLineButton("ecsitesusers", "id", EcosystemTranslator::Admins($lang));
        
        $tableHtml = $table->view($unitsArray, array("id" => "ID", "name" => CoreTranslator::Name($lang)));

        if ($table->isPrint()) {
            echo $tableHtml;
            return;
        }

        $this->render(array(
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit an unit form
     */
    public function editAction($id) {

        // get belonging info
        $site = array("id" => 0, "name" => "");
        if ($id > 0) {
            $site = $this->siteModel->get($id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "coresites/edit");
        $form->setTitle(EcosystemTranslator::Edit_Site($lang));
        $form->addHidden("id", $site["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $site["name"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "ecsitesedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "ecsites");

        if ($form->check()) {
            // run the database query
            $model = new EcSite();
            $model->set($form->getParameter("id"), $form->getParameter("name"));
            $this->redirect("ecsites");
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                'formHtml' => $formHtml
            ));
        }
    }

    public function usersAction($id_site) {

        $lang = $this->getLanguage();
        // get all the admins for a given site
        $siteAdmins = $this->siteModel->getSiteAdmins($id_site);

        $modelUser = new CoreUser();
        $users = $modelUser->getActiveUsers("name");
        $choicesU = array(); $choicesidU = array();
        foreach($users as $user){
            $choicesU[] = $user["name"] . " " . $user["firstname"];
            $choicesidU[] = $user["id"];
        }
        
        $admIds = array(); $admStatus = array();
        foreach($siteAdmins as $adm){
            $admStatus[] = $adm["id_status"];
            $admIds[] = $adm["id_user"];
            
        }

        $siteInfo = $this->siteModel->get($id_site);
        
        $form = new Form($this->request, "usersAction");
        $form->setTitle(EcosystemTranslator::Managers_for_site($lang) . ": " . $siteInfo["name"]);
        $form->addHidden("id_site", $siteInfo["id"]);
        
        $formAdd = new FormAdd($this->request, "usersActionList");
        $formAdd->addSelect("id_user", CoreTranslator::User($lang), $choicesU, $choicesidU, $admIds);
        $formAdd->addSelect("id_status", CoreTranslator::Status($lang), array(EcosystemTranslator::Manager($lang), EcosystemTranslator::Admin($lang)), array(3,4), $admStatus);
        $formAdd->setButtonsNames(CoreTranslator::Add(), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);  
        $form->setValidationButton(CoreTranslator::Save($lang), "ecsitesuserquery");
        $form->setButtonsWidth(2, 9);
        
        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array(
            'formHtml' => $formHtml
                ));
    }

    public function usersqueryAction() {

        $lang = $this->getLanguage();

        $id_site = $this->request->getParameter("id_site");
        $id_user = $this->request->getParameter("id_user");
        $id_status = $this->request->getParameter("id_status");

        $modelSite = new EcSite();
        $modelSite->removeSiteAdmins($id_site);

        for ($i = 0; $i < count($id_user); $i++) {
            $modelSite->addUserToSite($id_user[$i], $id_site, $id_status[$i]);
        }

        $_SESSION["message"] = EcosystemTranslator::Siteadminchangemessage($lang);
        $this->redirect("ecsitesusers/" .$id_site);
        //$this->siteusers($id_site);
    }

    /**
     * Remove an unit query to database
     */
    public function deleteAction($id) {

        $this->siteModel->delete($id);
        $this->redirect("ecsites");
    }

}
