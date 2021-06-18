<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CorePendingAccount.php';

require_once 'Framework/Email.php';

require_once 'Modules/core/Model/CoreTranslator.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CoreaccountController extends Controller {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {

        $lang = $this->getLanguage();

        $modelSpaces = new CoreSpace();
        $spaces = $modelSpaces->getForList();

        $form = new Form($this->request, "createaccountform");
        $form->setTitle(CoreTranslator::CreateAccount($lang));
        $form->addText("name", CoreTranslator::Name($lang), true);
        $form->addText("firstname", CoreTranslator::Firstname($lang), true);
        $form->addText("login", CoreTranslator::Login($lang), true);
        $form->addEmail("email", CoreTranslator::email($lang), true);
        $form->addText("phone", CoreTranslator::Phone($lang), false);
        $form->addSelectMandatory("id_space", CoreTranslator::AccessTo($lang), $spaces["names"], $spaces["ids"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "corecreateaccount");

        if ($form->check()) {

            $modelCoreUser = new CoreUser();

            if ($modelCoreUser->isLogin($form->getParameter("login"))) {
                $_SESSION["message"] = CoreTranslator::Error($lang) . ":" . CoreTranslator::LoginAlreadyExists($lang);
            } else {
                $pwd = $modelCoreUser->generateRandomPassword();

                $id_user = $modelCoreUser->createAccount(
                        $form->getParameter("login"),
                        $pwd,
                        $form->getParameter("name"),
                        $form->getParameter("firstname"),
                        $form->getParameter("email")
                );
                $modelCoreUser->setPhone($id_user, $form->getParameter("phone"));
                $modelPeningAccounts = new CorePendingAccount();
                $modelPeningAccounts->add($id_user, $form->getParameter("id_space"));

                $email = new Email();
                $mailParams = [
                    "email" => $form->getParameter("email"),
                    "login" => $form->getParameter("login"),
                    "pwd" => $pwd
                ];
                Configuration::getLogger()->debug("sending EMAIL", ["mailParams" => $mailParams]);
                $email->notifyUserByEmail($mailParams, "add_new_user", $lang);
                $this->redirect("coreaccountcreated");
                return;
            }
        }

        $modelConfig = new CoreConfig();
        $home_title = $modelConfig->getParam("home_title");
        $home_message = $modelConfig->getParam("home_message");


        return $this->render(array("home_title" => $home_title,
            "home_message" => $home_message,
            "formHtml" => $form->getHtml($lang)));
    }

    public function createdAction() {

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

}
