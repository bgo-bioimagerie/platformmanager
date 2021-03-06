<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Framework/FileUpload.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreBackupDatabase.php';
require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CorespaceadminController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
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
        
        //print_r($space);
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "corespaceadminedit");
        $form->setTitle(CoreTranslator::Edit_space($lang));
        
        $form->addText("name", CoreTranslator::Name($lang), true, $space["name"]);
        $form->addSelect("status", CoreTranslator::Status($lang), array(CoreTranslator::PrivateA($lang),CoreTranslator::PublicA($lang)), array(0,1), $space["status"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $space["color"]);
        $form->addUpload("image", CoreTranslator::Image($lang), $space["image"]);
        $form->addTextArea("description", CoreTranslator::Description($lang), false, $space["description"]);
        
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
            
            // set base informations
            $id = $modelSpace->setSpace($id, $this->request->getParameter("name"), 
                    $this->request->getParameter("status"),
                    $this->request->getParameter("color")
                    );
            
            $modelSpace->setDescription($id, $this->request->getParameter("description"));
            $modelSpace->setAdmins($id, $this->request->getParameter("admins"));
            
            // upload image
            $target_dir = "data/core/menu/";
            if ($_FILES["image"]["name"] != "") {
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

                $url = $id . "." . $ext;
                FileUpload::uploadFile($target_dir, "image", $url);

                $modelSpace->setImage($id, $target_dir . $url);
            }
            
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
