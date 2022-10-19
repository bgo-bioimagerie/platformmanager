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
class UseraccountController extends CoresecureController
{
    public function mainMenu()
    {
        $lang = $this->getLanguage();
        $dataView = [
            'bgcolor' => Constants::COLOR_WHITE,
            'color' => Constants::COLOR_BLACK,
            'My_Account' => CoreTranslator::My_Account($lang),
            'Informations' => CoreTranslator::Informations($lang),
            'Password' => CoreTranslator::Password($lang),
        ];
        return $this->twig->render("Modules/core/View/Coreusers/navbar.twig", $dataView);
    }
    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function indexAction()
    {
        $lang = $this->getLanguage();
        $idUser =  $_SESSION["id_user"];


        // Query to the database
        $modelUsersInfo = new UsersInfo();
        $userInfo = $modelUsersInfo->get($idUser);

        $modelCoreUser = new CoreUser();
        $userCore = $modelCoreUser->getUser($idUser);

        $form = new Form($this->request, "usermyaccountedit");
        $form->setTitle(UsersTranslator::Informations($lang));
        $form->addHidden("id", $idUser);
        $form->addText("firstname", CoreTranslator::Firstname($lang), true, $userCore["firstname"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $userCore["name"]);
        $form->addEmail("email", CoreTranslator::Email($lang), true, $userCore["email"], true);
        $form->addText("organization", CoreTranslator::Organization($lang), false, $userInfo["organization"] ?? "");
        $form->addText("unit", CoreTranslator::Unit($lang), false, $userInfo["unit"] ?? "");
        $form->addText("phone", UsersTranslator::Phone($lang), false, $userInfo["phone"] ?? "");
        $form->addUpload("avatar", UsersTranslator::Avatar($lang), $userInfo["avatar"] ?? "");
        $form->addTextArea("bio", UsersTranslator::Bio($lang), false, $userInfo["bio"] ?? "");

        $form->setValidationButton(CoreTranslator::Save($lang), "usersmyaccount");


        $formApi = new Form($this->request, "coremyaccountapikey");
        $formApi->setTitle('Api key');
        $formApi->addText("apikey", "Apikey", false, $userCore["apikey"], readonly: true);
        $formApi->setValidationButton('Reset', "usersmyaccount");

        $openid_providers = Configuration::get("openid", []);
        $providers = [];
        if (!empty($openid_providers)) {
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
            if ($linked == null) {
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
        if ($formApi->check()) {
            $modelCoreUser->newApiKey($_SESSION['id_user']);
            $_SESSION['flash'] = UsersTranslator::UserInformationsHaveBeenSaved($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("usersmyaccount");
            return;
        }

        if ($form->check()) {
            $modelCoreUser->editBaseInfo(
                $idUser,
                $form->getParameter("name"),
                $form->getParameter("firstname"),
                $form->getParameter("email")
            );
            $modelCoreUser->setPhone($idUser, $form->getParameter("phone"));
            $modelUsersInfo->set($idUser, $form->getParameter("phone"), $form->getParameter("unit"), $form->getParameter("organization"));
            $modelUsersInfo->setBio($idUser, $form->getParameter("bio"));

            // upload avatar
            $target_dir = "data/users/avatar/";
            if ($_FILES["avatar"]["name"] != "") {
                $ext = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);

                $url = $idUser . "." . $ext;
                FileUpload::uploadFile($target_dir, "avatar", $url);

                $modelUsersInfo->setAvatar($idUser, $target_dir . $url);
            }

            $_SESSION['flash'] = UsersTranslator::UserInformationsHaveBeenSaved($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("usersmyaccount");
            return;
        }

        $_SESSION["redirect"] = "usersmyaccount";

        unset($userCore['password']);
        // render the View
        $this->render(array(
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang),
            'formApi' => $formApi->getHtml($lang),
            'providers' => $providers,
            'linked' => $linked,
            'data' => ['user' => $userCore]
        ));
    }
}
