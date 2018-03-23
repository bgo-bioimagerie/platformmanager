<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/users/Model/UsersTranslator.php';
require_once 'Modules/users/Model/UsersInfo.php';

require_once 'Modules/core/Model/CoreUser.php';


/**
 * 
 * @author sprigent
 * Controller for the provider example of users module
 */
class UseraccountController extends CoresecureController {
    
    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction() {
        
        $lang = $this->getLanguage();
        $id_user =  $_SESSION["id_user"];
        

        // Query to the database
        $modelUser = new UsersInfo();
        $userInfo = $modelUser->get($id_user);
        
        $modelCoreUser = new CoreUser();
        $userCore = $modelCoreUser->getUser($id_user);

        $form = new Form($this->request, "usermyaccountedit");
        $form->setTitle(UsersTranslator::Informations($lang));
        
        $form->addText("firstname", CoreTranslator::Firstname($lang), true, $userCore["firstname"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $userCore["name"]);
        $form->addEmail("email", CoreTranslator::Email($lang), true, $userCore["email"]);
        
        $form->addText("unit", UsersTranslator::Unit($lang), false, $userInfo["unit"]);
        $form->addText("phone", UsersTranslator::Phone($lang), false, $userInfo["phone"]);
        $form->addUpload("avatar", UsersTranslator::Avatar($lang), $userInfo["avatar"]);
        $form->addTextArea("bio", UsersTranslator::Bio($lang), false, $userInfo["bio"]);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "usersmyaccount");
        
        if ( $form->check() ){
            
            $modelCoreUser->editBaseInfo($id_user,
                    $form->getParameter("name"),
                    $form->getParameter("firstname"),
                    $form->getParameter("email")
            );
            
            $modelUser->set($id_user, $form->getParameter("phone"), $form->getParameter("unit"));
            $modelUser->setBio($id_user, $form->getParameter("bio"));
            
            // upload avatar
            $target_dir = "data/users/avatar/";
            if ($_FILES["avatar"]["name"] != "") {
                $ext = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);

                $url = $id_user . "." . $ext;
                FileUpload::uploadFile($target_dir, "avatar", $url);

                $modelUser->setAvatar($id_user, $target_dir . $url);
            }
            
            $_SESSION["message"] = UsersTranslator::UserInformationsHaveBeenSaved($lang);
            $this->redirect("usersmyaccount");
            return;
            
        }
        
        // render the View
        $this->render(array(
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang)
        ));
    }

}
