<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrCompany.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingcompanyController extends CoresecureController {

    /**
     * User model object
     */
    private $companyModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->companyModel = new BrCompany();
        $_SESSION["openedNav"] = "breeding";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space) {

        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        $data = $this->companyModel->getForSpace($id_space);

        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(BreedingTranslator::CompanyInfo($lang), 3);
        $form->addText('name', BreedingTranslator::Name($lang), true, $data["name"]);
        $form->addTextArea('address', BreedingTranslator::Address($lang), true, $data["address"]);
        $form->addText('zipcode', BreedingTranslator::Zip_code($lang), true, $data["zipcode"]);
        $form->addText('city', BreedingTranslator::City($lang), true, $data["city"]);
        $form->addText('county', BreedingTranslator::County($lang), true, $data["county"]);
        $form->addText('country', BreedingTranslator::Country($lang), true, $data["country"]);
        $form->addText('tel', BreedingTranslator::Tel($lang), true, $data["tel"]);
        $form->addText('fax', BreedingTranslator::Fax($lang), true, $data["fax"]);
        $form->addText('email', BreedingTranslator::Email($lang), true, $data["email"]);
        $form->addText('approval_number', BreedingTranslator::ApprovalNumber($lang), true, $data["approval_number"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "brcompany/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->companyModel->set($id_space, $form->getParameter("name"), $form->getParameter("address"), $form->getParameter("zipcode"), $form->getParameter("city"), $form->getParameter("county"), $form->getParameter("country"), $form->getParameter("tel"), $form->getParameter("fax"), $form->getParameter("email"), $form->getParameter("approval_number")
            );

            $_SESSION["message"] = BreedingTranslator::Data_has_been_saved($lang);
            // after the provider is saved we redirect to the providers list page
            $this->redirect("brcompany/" . $id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // render the view
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }

}
