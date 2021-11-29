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
class InvoicesvisaController extends InvoicesController {

    private $visaModel;
    
    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $_SESSION["openedNav"] = "invoices";
        $this->visaModel = new InVisa();
        //$this->checkAuthorizationMenu("invoices");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(InvoicesTranslator::Visa($lang), 3);
        
        $table->addLineEditButton("invoicesvisaedit/" . $id_space);
        $table->addDeleteButton("invoicesvisadelete/" . $id_space, "id", "id");

        $headersArray = array(
            "id" => "ID",
            "user_name" => CoreTranslator::User($lang)
        );

        $modelVisa = new InVisa();
        $entriesArray = $modelVisa->getAll($id_space);
        $tableHtml = $table->view($entriesArray, $headersArray);

        // 
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
                ), "indexAction");
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if (!$id) {
            $value = array("id" => 0,  "id_user" => 0);
        } else {
            $value = $this->visaModel->get($id_space, $id);
        }

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(InvoicesTranslator::Visa($lang));

        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsersForSelect($id_space, "name");
        
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);
       
        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesvisaedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "invoicesvisas/" . $id_space);

        if ($form->check()) {
            $this->visaModel->set(
                $id,
                $this->request->getParameter("id_user"),
                $id_space);
            
            $this->redirect("invoicesvisas/" . $id_space);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $this->visaModel->delete($id_space, $id);
        $this->redirect("invoicesvisas/" . $id_space);
    }
}
