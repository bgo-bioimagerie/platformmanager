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
class ClientscompanyController extends ClientsController
{
    /**
     * User model object
     */
    private $companyModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->companyModel = new ClCompany();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $data = $this->companyModel->getForSpace($idSpace);
        $formKeys = ["name", "address", "zipcode", "city", "county", "country", "tel", "fax", "email", "approval_number"];
        foreach ($formKeys as $key) {
            $data[$key] = array_key_exists($key, $data) ? $data[$key] : "";
        }

        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(ClientsTranslator::CompanyInfo($lang), 3);
        $form->addText('name', ClientsTranslator::Name($lang), true, $data["name"]);
        $form->addTextArea('address', ClientsTranslator::Address($lang), true, $data["address"]);
        $form->addText('zipcode', ClientsTranslator::Zip_code($lang), true, $data["zipcode"]);
        $form->addText('city', ClientsTranslator::City($lang), true, $data["city"]);
        $form->addText('county', ClientsTranslator::County($lang), false, $data["county"]);
        $form->addText('country', ClientsTranslator::Country($lang), true, $data["country"]);
        $form->addText('tel', ClientsTranslator::Tel($lang), true, $data["tel"]);
        $form->addText('fax', ClientsTranslator::Fax($lang), false, $data["fax"]);
        $form->addText('email', ClientsTranslator::Email($lang), true, $data["email"]);
        $form->addText('approval_number', ClientsTranslator::ApprovalNumber($lang), true, $data["approval_number"]);

        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "clcompany/".$idSpace;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Ok($lang), $validationUrl);


        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->companyModel->set(
                $idSpace,
                $form->getParameter("name"),
                $form->getParameter("address"),
                $form->getParameter("zipcode"),
                $form->getParameter("city"),
                $this->request->getParameterNoException("county"),
                $form->getParameter("country"),
                $form->getParameter("tel"),
                $this->request->getParameterNoException('fax'),
                $form->getParameter("email"),
                $form->getParameter("approval_number")
            );

            $_SESSION["flash"] = ClientsTranslator::Data_has_been_saved($lang);
            $_SESSION["flashClass"] = 'success';

            if ($todo) {
                return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
            } else {
                // after the provider is saved we redirect to the providers list page
                return $this->redirect("clcompany/" . $idSpace);
            }
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // render the view
            $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }
}
