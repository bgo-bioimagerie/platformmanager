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
    public function indexAction($idSpace)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'quote', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'quote', 'book');
            return $this->redirect("quoteconfig/".$idSpace);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));

        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }

    public function pdftemplateAction($idSpace)
    {
        return $this->pdftemplate($idSpace, 'quote', new QuoteTranslator());
    }

    public function pdftemplatedeleteAction($idSpace, $name)
    {
        return $this->pdftemplatedelete($idSpace, 'quote', $name);
    }
}
