<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Model/InVisa.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/invoices/Controller/InvoicesController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class InvoicesvisaController extends InvoicesController
{
    private $visaModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        $this->visaModel = new InVisa();
        //$this->checkAuthorizationMenu("invoices");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("invoices", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(InvoicesTranslator::Visa($lang), 3);

        $table->addLineEditButton("invoicesvisaedit/" . $idSpace);
        $table->addDeleteButton("invoicesvisadelete/" . $idSpace, "id", "id");

        $headersArray = array(
            "id" => "ID",
            "user_name" => CoreTranslator::User($lang)
        );

        $modelVisa = new InVisa();
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
        $this->checkAuthorizationMenuSpace("invoices", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if (!$id) {
            $value = array("id" => 0,  "id_user" => 0);
        } else {
            $value = $this->visaModel->get($idSpace, $id);
        }

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(InvoicesTranslator::Visa($lang));

        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsersForSelect($idSpace, "name");

        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesvisaedit/" . $idSpace . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "invoicesvisas/" . $idSpace);

        if ($form->check()) {
            $this->visaModel->set(
                $id,
                $this->request->getParameter("id_user"),
                $idSpace
            );

            $this->redirect("invoicesvisas/" . $idSpace);
            return;
        }

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("invoices", $idSpace, $_SESSION["id_user"]);

        $this->visaModel->delete($idSpace, $id);
        $this->redirect("invoicesvisas/" . $idSpace);
    }
}
