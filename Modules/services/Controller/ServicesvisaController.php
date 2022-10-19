<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeVisa.php';
require_once 'Modules/services/Controller/ServicesController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ServicesvisaController extends ServicesController
{
    private $visaModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        $this->visaModel = new SeVisa();
        //$this->checkAuthorizationMenu("services");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Visa($lang), 3);

        $table->addLineEditButton("servicesvisaedit/" . $idSpace);
        $table->addDeleteButton("servicesvisadelete/" . $idSpace, "id", "id");

        $headersArray = array(
            "id" => "ID",
            "user_name" => CoreTranslator::User($lang)
        );

        $modelVisa = new SeVisa();
        $entriesArray = $modelVisa->getAll($idSpace);
        $tableHtml = $table->view($entriesArray, $headersArray);

        //
        $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'tableHtml' => $tableHtml,
                ), "indexAction");
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if (!$id) {
            $value = array("id" => 0,  "id_user" => 0);
        } else {
            $value = $this->visaModel->get($idSpace, $id);
        }

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(ServicesTranslator::Visa($lang));

        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsersForSelect($idSpace, "name");

        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesvisaedit/" . $idSpace . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "servicesvisas/" . $idSpace);

        if ($form->check()) {
            $id_visa = $this->visaModel->set($id, $this->request->getParameter("id_user"), $idSpace);

            return $this->redirect("servicesvisas/" . $idSpace, [], ['visa' => ['id' => $id_visa]]);
        }

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        $this->visaModel->delete($idSpace, $id);
        $this->redirect("servicesvisas/" . $idSpace);
    }
}
