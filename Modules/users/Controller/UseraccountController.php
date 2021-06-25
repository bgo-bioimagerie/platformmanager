<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';

require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/users/Model/UsersTranslator.php';
require_once 'Modules/users/Model/UsersInfo.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreOpenId.php';


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
        
        $form->addText("unit", UsersTranslator::Unit($lang), false, $userInfo["unit"] ?? "");
        $form->addText("phone", UsersTranslator::Phone($lang), false, $userInfo["phone"] ?? "");
        $form->addUpload("avatar", UsersTranslator::Avatar($lang), $userInfo["avatar"] ?? "");
        $form->addTextArea("bio", UsersTranslator::Bio($lang), false, $userInfo["bio"] ?? "");
        
        $form->addText("apikey", "Apikey", false, $userCore["apikey"], readonly: true);


        $form->setValidationButton(CoreTranslator::Save($lang), "usersmyaccount");
        
        $openid_providers = Configuration::get("openid", []);
        $providers = [];
        if(!empty($openid_providers)) {
            $nonce = uniqid("pfm");
            foreach ($openid_providers as $openid_provider) {
                $provider = [
                    "name" => $openid_provider,
                    "url" => Configuration::get("openid_${openid_provider}_url"),
                    "icon" => Configuration::get("openid_${openid_provider}_icon"),
                    "login" => Configuration::get("openid_${openid_provider}_login"),
                    "client_id" => Configuration::get("openid_${openid_provider}_client_id"),
                    "client_secret" => Configuration::get("openid_${openid_provider}_client_secret"),
                    "callback" => Configuration::get("public_url")."/ooc/$openid_provider/authorized",
                    "nonce" => $nonce,
                ];
                $providers[] = $provider;
                # list $providers
            }
        }
        $linked = [];
        try {
            $openidModel = new CoreOpenId();
            $linked = $openidModel->list($_SESSION['id_user']);
            if($linked == null) {
                $linked = [];
            }
        } catch(Exception $e) {
            Configuration::getLogger()->error('[openid][providers] could not get linked providers', ['error' => $e->getMessage()]);
        }

        Configuration::getLogger()->debug('[openid][providers]', [
            'providers' => $providers,
            'linked' => $linked,
            'id_user' => $_SESSION['id_user']
        ]);

        // get user linked providers and display them with unlink


        if ( $form->check() ){
            
            $modelCoreUser->editBaseInfo($id_user,
                    $form->getParameter("name"),
                    $form->getParameter("firstname"),
                    $form->getParameter("email")
            );
            $modelCoreUser->setPhone($id_user, $form->getParameter("phone"));
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
        
        $_SESSION["redirect"] = "usersmyaccount";
        // render the View
        $this->render(array(
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang),
            'providers' => $providers,
            'linked' => $linked
        ));
    }

}
