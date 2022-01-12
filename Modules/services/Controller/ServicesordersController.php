<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/services/Controller/ServicesController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesordersController extends ServicesController {

    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        
        $this->serviceModel = new SeOrder();
        //$this->checkAuthorizationMenu("services");

    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $status = "") {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get sort action
        $sortentry = "id";

        // get the commands list
        $modelEntry = new SeOrder();
        $entriesArray = array();
        if ($status == "") {
            if (isset($_SESSION["supplies_lastvisited"])) {
                $status = $_SESSION["supplies_lastvisited"];
            } else {
                $status = "all";
            }
        }

        if ($status == "all") {
            $entriesArray = $modelEntry->entries($id_space, $sortentry);
        } else if ($status == "opened") {
            $entriesArray = $modelEntry->openedEntries($id_space, $sortentry);
        } else if ($status == "closed") {
            $entriesArray = $modelEntry->closedEntries($id_space, $sortentry);
        }

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Services_Orders($lang), 3);
        $table->addLineEditButton("servicesorderedit/" . $id_space);
        $table->addDeleteButton("servicesorderdelete/" . $id_space, "id", "no_identification");

        $headersArray = array(
            "no_identification" => ServicesTranslator::No_identification($lang),
            "user_name" => CoreTranslator::User($lang),
            "id_status" => CoreTranslator::Status($lang),
            "date_open" => ServicesTranslator::Opened_date($lang),
            "date_close" => ServicesTranslator::Closed_date($lang),
            "date_last_modified" => ServicesTranslator::Last_modified_date($lang),
        );


        for ($i = 0; $i < count($entriesArray); $i++) {
            if ($entriesArray[$i]["id_status"]) {
                $entriesArray[$i]["id_status"] = ServicesTranslator::Opened($lang);
            } else {
                $entriesArray[$i]["id_status"] = ServicesTranslator::Closed($lang);
            }
            $entriesArray[$i]["date_open"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_open"], $lang);
            $entriesArray[$i]["date_close"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_close"], $lang);
            $entriesArray[$i]["date_last_modified"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_last_modified"], $lang);
        }
        $tableHtml = $table->view($entriesArray, $headersArray);

        if ($table->isPrint()) {
            echo $tableHtml;
            return;
        }

        // 
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
                ), "indexAction");
    }

    public function openedAction($id_space) {
        $_SESSION["supplies_lastvisited"] = "opened";
        $this->indexAction($id_space, "opened");
    }

    public function closedAction($id_space) {
        $_SESSION["supplies_lastvisited"] = "closed";
        $this->indexAction($id_space, "closed");
    }

    public function AllAction($id_space) {

        $_SESSION["supplies_lastvisited"] = "all";
        $this->indexAction($id_space, "all");
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->serviceModel->delete($id_space, $id);
        $this->redirect("servicesorders/" . $id_space);
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = new Form($this->request, "orderEditForm");
        $form->setTitle(ServicesTranslator::Edit_order($lang), 3);

        $modelOrder = new SeOrder();
        $clientSelect['choices'] = [""];
        $clientSelect['choicesid'] = [""];
        $clientSelect['value'] = "";

        if ($id > 0) {
            $value = $modelOrder->getEntry($id_space, $id);
            $items = $modelOrder->getOrderServices($id_space, $id);
            $modelClientUser = new ClClientUser();
            $userClients = $modelClientUser->getUserClientAccounts($value['id_user'], $id_space) ?: [];
            foreach($userClients as $client) {
                array_push($clientSelect['choices'], $client['name']);
                array_push($clientSelect['choicesid'], $client['id']);
            }
            $clientSelect['value'] = ($value['id_client'] != 0) ? $value['id_client'] : $userClients[0]['id'] ?? "";
        } else {
            $value = $modelOrder->defaultEntryValues();
            $items = array("services" => array(), "quantities" => array(), "quantity_types" => array());
        }

        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsersForSelect($id_space, "name");

        $form->addSeparator(CoreTranslator::Description($lang));
        $form->addText("no_identification", ServicesTranslator::No_identification($lang), false, $value["no_identification"]);
        $form->addSelectMandatory("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);
        $form->addSelectMandatory('id_client', ClientsTranslator::Client($lang), $clientSelect['choices'], $clientSelect['choicesid'], $clientSelect['value']);
        $form->addSelect("id_status", CoreTranslator::Status($lang), array(CoreTranslator::Open($lang), CoreTranslator::Close($lang)), array(1, 0), $value["id_status"]);

        $form->addDate("date_open", ServicesTranslator::Opened_date($lang), false, $value["date_open"]);
        $form->addDate("date_close", ServicesTranslator::Closed_date($lang), false, $value["date_close"]);
        
        if ($id > 0) {
            $form->addText("date_last_modified", ServicesTranslator::Last_modified_date($lang), false, CoreTranslator::dateFromEn($value["date_last_modified"], $lang), "disabled");
            $form->addText("created_by", ServicesTranslator::Created_by($lang), false, $modelUser->getUserFUllName($value["created_by_id"]), "disabled");
            $form->addText("modified_by_id", ServicesTranslator::Modified_by($lang), false, $modelUser->getUserFUllName($value["modified_by_id"]), "disabled");
        }
        
        $modelServices = new SeService();
        $services = $modelServices->getForList($id_space);


        $formAdd = new FormAdd($this->request, "orderEditForm");
        $formAdd->addSelect("services", ServicesTranslator::services($lang), $services["names"], $services["ids"], $items["services"]);
        $formAdd->addFloat("quantities", ServicesTranslator::Quantity($lang), $items["quantities"]);
        $formAdd->addLabel("type", $items["quantity_types"]);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form->addSeparator(ServicesTranslator::Services_list($lang));
        $form->setFormAdd($formAdd);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesorderedit/" . $id_space . "/" . $id);
        $form->setButtonsWidth(2, 10);

        if ($form->check()) {
            $id_order = $modelOrder->setOrder(
                    $id,
                    $id_space,
                    $this->request->getParameter("id_user"), 
                    $this->request->getParameter("no_identification"), 
                    $_SESSION["id_user"], 
                    CoreTranslator::dateToEn($this->request->getParameter("date_open"), $lang), 
                    date("Y-m-d", time()), 
                    CoreTranslator::dateToEn($this->request->getParameter("date_close"), $lang)
                );
                
            $modelOrder->setModifiedBy($id_space, $id, $_SESSION["id_user"]);
            
            $servicesIds = $this->request->getParameter("services");
            $servicesQuantities = $this->request->getParameter("quantities");

            for ($i = 0; $i < count($servicesQuantities); $i++) {
                $qOld = !$id ? 0 : $modelOrder->getOrderServiceQuantity($id_space ,$id, $servicesIds[$i]);
                $qDelta = $servicesQuantities[$i] - $qOld[0];
                $modelServices->editquantity($id_space, $servicesIds[$i], $qDelta, "subtract");
                $modelOrder->setService($id_space, $id_order, $servicesIds[$i], $servicesQuantities[$i]);
            }

            $this->redirect("servicesorders/" . $id_space);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

}
