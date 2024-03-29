<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/mailer/Model/MailerInstall.php';
require_once 'Modules/mailer/Model/MailerTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreTranslator.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class MailerconfigController extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space)
    {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'mailer', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'mailer', 'envelope');
            return $this->redirect("mailerconfig/".$id_space);
        }

        $formEdit = $this->EditForm($lang, $id_space);
        if ($formEdit->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("mailerEdit", $this->request->getParameter('mailerEdit'), $id_space);
            return $this->redirect("mailerconfig/".$id_space);
        }

        // cf issue #735
        /* $mailerSetCopyToFrom = $this->mailerSetCopyToFromForm($lang, $id_space);
        if ($mailerSetCopyToFrom->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam(
                "MailerSetCopyToFrom",
                $this->request->getParameter('mailerSetCopyToFrom'),
                $id_space
            );
            $this->redirect("mailerconfig/".$id_space);
            return;
        } */

        // view
        $forms = array(
            $formMenusactivation->getHtml($lang),
            $formEdit->getHtml($lang)
            /* $mailerSetCopyToFrom->getHtml($lang) (cf issue #735) */
        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function mailerSetCopyToFromForm($lang, $id_space)
    {
        $modelConfig = new CoreConfig();
        $mailerSetCopyToFrom = $modelConfig->getParamSpace("MailerSetCopyToFrom", $id_space, 0);

        $form = new Form($this->request, "mailerSetCopyToFromForm");
        $form->addSeparator(MailerTranslator::SendCopyToSender($lang));

        $form->addSelect(
            "mailerSetCopyToFrom",
            "",
            array(CoreTranslator::yes($lang),
            CoreTranslator::no($lang)),
            array(1,0),
            $mailerSetCopyToFrom
        );

        $form->setValidationButton(CoreTranslator::Save($lang), "mailerconfig/".$id_space);

        return $form;
    }

    protected function EditForm($lang, $id_space)
    {
        $modelConfig = new CoreConfig();
        $mailEdit = $modelConfig->getParamSpace("mailerEdit", $id_space, CoreSpace::$ADMIN);

        $form = new Form($this->request, "mailerEditForm");
        $form->addSeparator(CoreTranslator::EditionAccess($lang));

        $form->addSelect(
            "mailerEdit",
            "",
            array(CoreTranslator::Manager($lang),
            CoreTranslator::Admin($lang)),
            array(CoreSpace::$MANAGER, CoreSpace::$ADMIN),
            $mailEdit
        );

        $form->setValidationButton(CoreTranslator::Save($lang), "mailerconfig/".$id_space);

        return $form;
    }
}
