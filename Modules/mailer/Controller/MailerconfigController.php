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
    public function indexAction($idSpace)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'mailer', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'mailer', 'envelope');
            return $this->redirect("mailerconfig/".$idSpace);
        }

        $formEdit = $this->EditForm($lang, $idSpace);
        if ($formEdit->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("mailerEdit", $this->request->getParameter('mailerEdit'), $idSpace);
            return $this->redirect("mailerconfig/".$idSpace);
        }

        // cf issue #735
        /* $mailerSetCopyToFrom = $this->mailerSetCopyToFromForm($lang, $idSpace);
        if ($mailerSetCopyToFrom->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam(
                "MailerSetCopyToFrom",
                $this->request->getParameter('mailerSetCopyToFrom'),
                $idSpace
            );
            $this->redirect("mailerconfig/".$idSpace);
            return;
        } */

        // view
        $forms = array(
            $formMenusactivation->getHtml($lang),
            $formEdit->getHtml($lang)
            /* $mailerSetCopyToFrom->getHtml($lang) (cf issue #735) */
        );
        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }

    protected function mailerSetCopyToFromForm($lang, $idSpace)
    {
        $modelConfig = new CoreConfig();
        $mailerSetCopyToFrom = $modelConfig->getParamSpace("MailerSetCopyToFrom", $idSpace, 0);

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

        $form->setValidationButton(CoreTranslator::Save($lang), "mailerconfig/".$idSpace);

        return $form;
    }

    protected function EditForm($lang, $idSpace)
    {
        $modelConfig = new CoreConfig();
        $mailEdit = $modelConfig->getParamSpace("mailerEdit", $idSpace, CoreSpace::$ADMIN);

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

        $form->setValidationButton(CoreTranslator::Save($lang), "mailerconfig/".$idSpace);

        return $form;
    }
}
