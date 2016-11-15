<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreBackupDatabase.php';
require_once 'Modules/core/Model/CoreSpace.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CorespaceadminController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new Exception("Error 503: Permission denied");
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();
        
        $table = new TableView();
        $table->setTitle(CoreTranslator::Spaces($lang), 3);
        
        $modelSpace = new CoreSpace();
        $data = $modelSpace->getSpaces("name");
        for($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["url"] = "corespace/" . $data[$i]["id"];
        }
        
        $headers = array("name" => CoreTranslator::Name($lang), "status" => CoreTranslator::Status($lang),
                         "url" => CoreTranslator::Url($lang));
        
        $table->addLineEditButton("spaceadminedit");
        $table->addDeleteButton("spaceadmindelete");
        $tableHtml = $table->view($data, $headers);
        
        $this->render(array("lang" => $lang, "tableHtml" => $tableHtml));
    }
    
    public function editAction($id){
        
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id);
        $spaceAdmins = $modelSpace->spaceAdmins($id);
        
        //print_r($spaceAdmins);
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "corespaceadminedit");
        $form->setTitle(CoreTranslator::Edit_space($lang));
        
        $form->addText("name", CoreTranslator::Name($lang), true, $space["name"]);
        $form->addSelect("status", CoreTranslator::Status($lang), array(CoreTranslator::PrivateA($lang),CoreTranslator::PublicA($lang)), array(0,1), $space["status"]);
        
        $modelUser = new CoreUser();
        $users = $modelUser->getActiveUsers("name");
        $usersNames = array(); $usersIds = array();
        foreach($users as $user){
            $usersNames[] = $user["name"] . " " . $user["firstname"];
            $usersIds[] = $user["id"];
        }
        
        $formAdd = new FormAdd($this->request, "addformspaceedit");
        $formAdd->addSelect("admins", CoreTranslator::Admin($lang), $usersNames, $usersIds, $spaceAdmins);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        
        $form->setFormAdd($formAdd, CoreTranslator::Admin($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "spaceadminedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "spaceadmin");
        
        if ($form->check()){
            
            //echo "name = " . $this->request->getParameter("name") . "<br/>";
            //echo "status = " . $this->request->getParameter("status") . "<br/>";
            //echo "id = " . $id . "<br/>";
            
            $id = $modelSpace->setSpace($id, $this->request->getParameter("name"), $this->request->getParameter("status"));
            
            $modelSpace->setAdmins($id, $this->request->getParameter("admins"));
            
            
            $this->redirect("spaceadmin");
            return;
        }
        
        $this->render(array("lang" => $lang, "formHtml" => $form->getHtml($lang)));
        
    }
    
    public function deleteAction($id){
        
        $model = new CoreSpace();
        $model->delete($id);
        $this->redirect("spaceadmin");
        
    }

}
