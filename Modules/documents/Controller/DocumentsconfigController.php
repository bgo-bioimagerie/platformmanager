<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/documents/Model/DocumentsInstall.php';
require_once 'Modules/documents/Model/DocumentsTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class DocumentsconfigController extends CoresecureController
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
        $formMenusactivation = $this->menusactivationForm($idSpace, 'documents', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'documents', 'folder2-open');
            return $this->redirect("documentsconfig/".$idSpace);
        }

        $modelCoreConfig = new CoreConfig();
        $formEdit = new Form($this->request, "documentsEditForm");
        if ($formEdit->check()) {
            $modelCoreConfig->setParam('documentsEdit', $this->request->getParameter('documentsEdit'), $idSpace);
            $documentsEdit = $this->request->getParameter('documentsEdit');
        } else {
            $documentsEdit = $modelCoreConfig->getParamSpace("documentsEdit", $idSpace, CoreSpace::$MANAGER);
        }

        $formEdit->addSeparator(CoreTranslator::EditionAccess($lang));
        $formEdit->addSelect("documentsEdit", "Edit", array(CoreTranslator::Manager($lang), CoreTranslator::Admin($lang)), array(CoreSpace::$MANAGER, CoreSpace::$ADMIN), $documentsEdit);
        $formEdit->setValidationButton(CoreTranslator::Save($lang), "documentsconfig/".$idSpace);



        $forms = array($formMenusactivation->getHtml($lang), $formEdit->getHtml($lang));

        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }
}
