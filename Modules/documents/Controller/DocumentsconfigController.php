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
    public function indexAction($id_space)
    {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'documents', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'documents', 'folder2-open');
            return $this->redirect("documentsconfig/".$id_space);
        }

        $modelCoreConfig = new CoreConfig();
        $formEdit = new Form($this->request, "documentsEditForm");
        if ($formEdit->check()) {
            $modelCoreConfig->setParam('documentsEdit', $this->request->getParameter('documentsEdit'), $id_space);
            $documentsEdit = $this->request->getParameter('documentsEdit');
        } else {
            $documentsEdit = $modelCoreConfig->getParamSpace("documentsEdit", $id_space, CoreSpace::$MANAGER);
        }

        $formEdit->addSeparator(CoreTranslator::EditionAccess($lang));
        $formEdit->addSelect("documentsEdit", "Edit", array(CoreTranslator::Manager($lang), CoreTranslator::Admin($lang)), array(CoreSpace::$MANAGER, CoreSpace::$ADMIN), $documentsEdit);
        $formEdit->setValidationButton(CoreTranslator::Save($lang), "documentsconfig/".$id_space);



        $forms = array($formMenusactivation->getHtml($lang), $formEdit->getHtml($lang));

        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }
}
