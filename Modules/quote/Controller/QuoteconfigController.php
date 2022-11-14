<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/quote/Model/QuoteInstall.php';
require_once 'Modules/quote/Model/QuoteTranslator.php';
require_once 'Modules/quote/Controller/QuotelistController.php';

require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Controller/CoreabstractpdftemplateController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class QuoteconfigController extends PfmTemplateController
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
        $formMenusactivation = $this->menusactivationForm($id_space, 'quote', $lang, CoreSpace::$MANAGER);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'quote', 'book');
            return $this->redirect("quoteconfig/".$id_space);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));

        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    public function pdftemplateAction($id_space)
    {
        return $this->pdftemplate($id_space, 'quote', new QuoteTranslator());
    }

    public function pdftemplatedeleteAction($id_space, $name)
    {
        return $this->pdftemplatedelete($id_space, 'quote', $name);
    }
}
