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

        // get sort action
        $sortentry = "id";
        if ($this->request->isParameterNotEmpty('actionid')) {
            $sortentry = $this->request->getParameter("actionid");
        }

        // get the user list
        $unitsArray = $this->siteModel->getAll($sortentry);

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

        // get all the admins for a given site
        $siteAdmins = $this->siteModel->getSiteAdmins($id_site);

        $modelUser = new CoreUser();
        $users = $modelUser->getActiveUsers("name");

        $siteInfo = $this->siteModel->get($id_site);

        // view
        $lang = $this->getLanguage();
        $this->render(array(
            'admins' => $siteAdmins,
            'users' => $users,
            'siteInfo' => $siteInfo,
            'lang' => $lang
                ));
    }

    public function siteusersquery() {

        $lang = $this->getLanguage();

        $id_site = $this->request->getParameter("id_site");
        $id_user = $this->request->getParameter("id_user");
        $id_status = $this->request->getParameter("id_status");

        $modelSite = new CoreSite();
        $modelSite->removeSiteAdmins($id_site);

        for ($i = 0; $i < count($id_user); $i++) {
            $modelSite->addUserToSite($id_user[$i], $id_site, $id_status[$i]);
        }

        $_SESSION["message"] = CoreTranslator::Siteadminchangemessage($lang);
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
