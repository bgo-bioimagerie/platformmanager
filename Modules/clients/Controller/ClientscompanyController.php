<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClCompany.php';
require_once 'Modules/clients/Controller/ClientsController.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class ClientscompanyController extends ClientsController {

    /**
     * User model object
     */
    private $companyModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->companyModel = new ClCompany();

    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space) {

        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        $data = $this->companyModel->getForSpace($id_space);

        // form
        // build the form

        $formKeys = ["name", "address", "zipcode", "city", "county", "country", "tel", "fax", "email", "approval_number"];
        foreach ($formKeys as $key) {
            $data[$key] = array_key_exists($key, $data) ?: "";
        }

        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(ClientsTranslator::CompanyInfo($lang), 3);
        $form->addText('name', ClientsTranslator::Name($lang), true, $data["name"]);
        $form->addTextArea('address', ClientsTranslator::Address($lang), true, $data["address"]);
        $form->addText('zipcode', ClientsTranslator::Zip_code($lang), true, $data["zipcode"]);
        $form->addText('city', ClientsTranslator::City($lang), true, $data["city"]);
        $form->addText('county', ClientsTranslator::County($lang), true, $data["county"]);
        $form->addText('country', ClientsTranslator::Country($lang), true, $data["country"]);
        $form->addText('tel', ClientsTranslator::Tel($lang), true, $data["tel"]);
        $form->addText('fax', ClientsTranslator::Fax($lang), true, $data["fax"]);
        $form->addText('email', ClientsTranslator::Email($lang), true, $data["email"]);
        $form->addText('approval_number', ClientsTranslator::ApprovalNumber($lang), true, $data["approval_number"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "clcompany/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->companyModel->set($id_space, $form->getParameter("name"), $form->getParameter("address"), $form->getParameter("zipcode"), $form->getParameter("city"), $form->getParameter("county"), $form->getParameter("country"), $form->getParameter("tel"), $form->getParameter("fax"), $form->getParameter("email"), $form->getParameter("approval_number")
            );

            $_SESSION["message"] = ClientsTranslator::Data_has_been_saved($lang);
            // after the provider is saved we redirect to the providers list page
            $this->redirect("clcompany/" . $id_space);
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
