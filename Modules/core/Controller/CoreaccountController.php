<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreMainMenu.php';
require_once 'Modules/core/Model/CoreMainSubMenu.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CorePendingAccount.php';

// Dependency with UsersInfo class
require_once 'Modules/users/Model/UsersInfo.php';

require_once 'Framework/Email.php';

require_once 'Modules/core/Model/CoreTranslator.php';

use Firebase\JWT\JWT;

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CoreaccountController extends Controller
{
    public function confirmAction()
    {
        $lang = $this->getLanguage();
        $token = $this->request->getParameter("token");
        try {
            $decoded = JWT::decode($token, Configuration::get('jwt_secret'), array('HS256'));
        } catch(Throwable $e) {
            Configuration::getLogger()->debug('[core][account][confirm] jwt decode failed', ['error' => $e->getMessage()]);
            return new PfmAuthException($e->getMessage(), 403);
        }
        $decoded_array = (array) $decoded;
        $data = (array) $decoded_array['data'];
        Configuration::getLogger()->debug('[account] registration confirmation', ['user' => $data]);

        $modelCoreUser = new CoreUser();
        $modelUsersInfo = new UsersInfo();

        if ($modelCoreUser->isLogin($data["login"])) {
            throw new PfmUserException("Error:". CoreTranslator::LoginAlreadyExists($lang), 403);
        }
        $pwd = $modelCoreUser->generateRandomPassword();

        $id_user = $modelCoreUser->createAccount(
            $data["login"],
            $pwd,
            $data["name"],
            $data["firstname"],
            $data["email"]
        );
        if ($data["phone"]??"") {
            $modelCoreUser->setPhone($id_user, $data["phone"]);
        }
        $modelUsersInfo->set(
            $id_user,
            $data["phone"] ?? '',
            $data['unit'] ?? '',
            $data['organization'] ?? ''
        );
        // if specified a space, add to pending users in space
        if (array_key_exists('id_space', $data) && $data['id_space']) {
            $modelPeningAccounts = new CorePendingAccount();
            $modelPeningAccounts->add($id_user, $data["id_space"]);
        }
        // validate anyway the account
        $modelCoreUser->validateAccount($id_user);
        $userFullName = $modelCoreUser->getUserFUllName($id_user);

        $email = new Email();
        $mailParams = [
            "email" => $data["email"],
            "login" => $data["login"],
            "fullName" => $userFullName,
            "name" => $data["name"],
            "pwd" => $pwd,
            "supData" => $data
        ];
        $email->notifyUserByEmail($mailParams, "add_new_user", $lang);
        $email->notifyAdminsByEmail($mailParams, "self_registration", $lang);

        $this->redirect("coreaccountcreated");
    }

    public function waitingAction()
    {
        $lang = $this->getLanguage();
        $message = CoreTranslator::WaitingAccountMessage($lang);

        return $this->render(array(
            "message" => $message
        ));
    }

    /**
     * Adds structures (i.e. menus) names to spaces displayed names
     * @param Array $spacesList {"id" => string, "name" => string}
     * @return Array
     */
    protected function addMenuNamesToSpaceNames($spacesList)
    {
        $modelMenus = new CoreMainMenu();
        $modelSubMenus = new CoreMainSubMenu();
        $modelMenuItems = new CoreMainMenuItem();

        $menus = $modelMenus->getAll();
        $subMenus = $modelSubMenus->getAll();
        $items = $modelMenuItems->getAll();

        for ($i=0; $i<count($spacesList['ids']); $i++) {
            $idSpace = $spacesList['ids'][$i];
            for ($j=0; $j<count($items); $j++) {
                // get id subMenu
                if ($items[$j]['id_space'] && $items[$j]['id_space'] == $idSpace) {
                    $idSubMenu =  $items[$j]['id_sub_menu'];
                    for ($k=0; $k<count($subMenus); $k++) {
                        // get id menu
                        if ($subMenus[$k]['id'] && $subMenus[$k]['id'] == $idSubMenu) {
                            $idMenu =  $subMenus[$k]['id_main_menu'];
                            // add menu name
                            for ($l=0; $l<count($menus); $l++) {
                                if ($menus[$l]['id'] && $menus[$l]['id'] == $idMenu) {
                                    $spacesList['names'][$i] .= (" - " . $menus[$l]['name']);
                                    break;
                                }
                            }
                            break;
                        }
                    }
                    break;
                }
            }
        }
        return $spacesList;
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction()
    {
        if (! Configuration::get('allow_registration', false)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }

        $lang = $this->getLanguage();
        $modelSpaces = new CoreSpace();
        $spaces = $modelSpaces->getForList();
        $spaces = $this->addMenuNamesToSpaceNames($spaces);

        $form = new Form($this->request, "createaccountform");
        $form->setTitle(CoreTranslator::CreateAccount($lang));
        $form->addText("name", CoreTranslator::Name($lang), true);
        $form->addText("firstname", CoreTranslator::Firstname($lang), true);
        $form->addText("login", CoreTranslator::Login($lang), true, checkUnicity: true, suggestLogin: true);
        $form->addEmail("email", CoreTranslator::email($lang), true, checkUnicity: true);
        // $name have to be like "confirm_[fieldname]" to compare the 2 values
        $form->addEmail("confirm_email", CoreTranslator::Confirm_email($lang), true);
        $form->addText("phone", CoreTranslator::Phone($lang), false);
        $form->addText("organization", CoreTranslator::Organization($lang), false);
        $form->addText("unit", CoreTranslator::Unit($lang), false);
        $form->addSelectMandatory("id_space", CoreTranslator::AccessTo($lang), $spaces["names"], $spaces["ids"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "corecreateaccount");

        if ($form->check()) {
            $modelCoreUser = new CoreUser();

            if ($modelCoreUser->isLogin($form->getParameter("login"))) {
                $_SESSION['flash'] = CoreTranslator::Error($lang) . ":" . CoreTranslator::LoginAlreadyExists($lang);
                $this->redirect("corecreateaccount");
                return;
            }

            $payload = array(
                "iss" => Configuration::get('public_url', ''),
                "aud" => Configuration::get('public_url', ''),
                "exp" => time() + 3600*24*2, // 2 days to confirm
                "data" => [
                    "login" => $form->getParameter("login"),
                    "name" => $form->getParameter("name"),
                    "firstname" => $form->getParameter("firstname"),
                    "email" => $form->getParameter("email"),
                    "phone" => $form->getParameter("phone") ?? '',
                    "organization" => $form->getParameter("organization") ?? '',
                    "unit" => $form->getParameter("unit") ?? '',
                    "id_space" => $form->getParameter("id_space")
                ]
            );

            /**
             * IMPORTANT:
             * You must specify supported algorithms for your application. See
             * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
             * for a list of spec-compliant algorithms.
             */
            $jwt = JWT::encode($payload, Configuration::get('jwt_secret'));
            $email = new Email();
            $mailParams = [
                "jwt" => $jwt,
                "url" => Configuration::get('public_url'),
                "email" => $form->getParameter("email"),
                "supData" => $payload['data']
            ];
            $email->notifyUserByEmail($mailParams, "add_new_user_waiting", $lang);
            $this->redirect("coreuserwaiting");
            return;
        }

        $modelConfig = new CoreConfig();
        $home_title = $modelConfig->getParam("home_title");
        $home_message = $modelConfig->getParam("home_message");


        $this->render(array("home_title" => $home_title,
            "home_message" => $home_message,
            "formHtml" => $form->getHtml($lang)));
    }

    public function createdAction()
    {
        $modelConfig = new CoreConfig();
        $home_title = $modelConfig->getParam("home_title");
        $home_message = $modelConfig->getParam("home_message");

        $lang = $this->getLanguage();
        $message = CoreTranslator::CreatedAccountMessage($lang);

        return $this->render(array("home_title" => $home_title,
            "home_message" => $home_message,
            "message" => $message
        ));
    }

    public function isuniqueAction()
    {
        $params = $this->request->params();
        $type = $params["type"];
        $value = $params["value"];
        $id_user = $params["user"] ?? 0;
        $modelUser = new CoreUser();
        $email = "";
        $login = "";
        if ($id_user && $id_user > 0) {
            $user = $modelUser->getInfo($id_user);
            $email = $user['email'];
            $login = $user['login'];
        }
        if ($type === "email") {
            $isUnique = !$modelUser->isEmail($value, $email);
        } elseif ($type === "login") {
            $isUnique = !$modelUser->isLogin($value, $login);
        } else {
            Configuration::getLogger()->error("[coreaccount:isunique] Invalid type received", ["type" => $type]);
            $isUnique = false;
        }
        $this->render(['data' => ['isUnique' => $isUnique]]);
    }
}
