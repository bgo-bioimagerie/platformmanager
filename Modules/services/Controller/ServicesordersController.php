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
        $data = array();
        if ($status == "") {
            if (isset($_SESSION["supplies_lastvisited"])) {
                $status = $_SESSION["supplies_lastvisited"];
            } else {
                $status = "all";
            }
        }

        if ($status == "all") {
            $data = $modelEntry->entries($id_space, $sortentry);
        } else if ($status == "opened") {
            $data = $modelEntry->openedEntries($id_space, $sortentry);
        } else if ($status == "closed") {
            $data = $modelEntry->closedEntries($id_space, $sortentry);
        }

        $entriesArray = $data;

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Services_Orders($lang), 3);
        $table->addLineEditButton("servicesorderedit/" . $id_space);
        $table->addDeleteButton("servicesorderdelete/" . $id_space, "id", "no_identification");

        $headersArray = array(
            "no_identification" => ServicesTranslator::No_identification($lang),
            "user_name" => CoreTranslator::User($lang),
            "client_name" => ClientsTranslator::Client($lang),
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
            'tableHtml' => $tableHtml,
            'data' => ['orders' => $data]
        ), "indexAction");
    }

    public function openedAction($id_space) {
        $_SESSION["supplies_lastvisited"] = "opened";
        return $this->indexAction($id_space, "opened");
    }

    public function closedAction($id_space) {
        $_SESSION["supplies_lastvisited"] = "closed";
        return $this->indexAction($id_space, "closed");
    }

    public function AllAction($id_space) {
        $_SESSION["supplies_lastvisited"] = "all";
        return $this->indexAction($id_space, "all");
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->serviceModel->delete($id_space, $id);
        return $this->redirect("servicesorders/" . $id_space, ['order' => ['id' => $id]]);
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

            if($value['id_resp'] && !in_array($value['id_resp'], $clientSelect['choicesid'])){
                $modelCl = new ClClient();
                $clName = $modelCl->getName($id_space, $value['id_resp']);
                if(!$clName) {
                    $clName = Constants::UNKNOWN;
                }
                array_push($clientSelect['choices'], '[!] '.$clName);
                array_push($clientSelect['choicesid'], $value['id_resp']);
            }


            $clientSelect['value'] = ($value['id_resp'] != 0) ? $value['id_resp'] : $userClients[0]['id'] ?? "";
        } else {
            $value = $modelOrder->defaultEntryValues();
            $items = array("services" => array(), "quantities" => array(), "quantity_types" => array());
        }

        $modelUser = new CoreUser();
        $modelClient = new ClClient();
        $users = $modelUser->getSpaceActiveUsersForSelect($id_space, "name");
        $clients = $modelClient->getForList($id_space);

        $form->addSeparator(CoreTranslator::Description($lang));
        $form->addText("no_identification", ServicesTranslator::No_identification($lang), false, $value["no_identification"]);
        $form->addSelectMandatory("id_client", ClientsTranslator::ClientAccount($lang), $clients["names"], $clients["ids"], $clientSelect['value']);
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);
        $form->addSelect("id_status", CoreTranslator::Status($lang), array(CoreTranslator::Open($lang), CoreTranslator::Closed($lang)), array(1, 0), $value["id_status"]);

        $form->addDate("date_open", ServicesTranslator::Opened_date($lang), false, $value["date_open"]);
        $form->addDate("date_close", ServicesTranslator::Closed_date($lang), false, $value["date_close"]);
        
        if ($id > 0) {
            $form->addText("date_last_modified", ServicesTranslator::Last_modified_date($lang), false, CoreTranslator::dateFromEn($value["date_last_modified"], $lang), "disabled");
            $form->addText("created_by", ServicesTranslator::Created_by($lang), false, $modelUser->getUserFUllName($value["created_by_id"]), "disabled");
            $form->addText("modified_by_id", ServicesTranslator::Modified_by($lang), false, $modelUser->getUserFUllName($value["modified_by_id"]), "disabled");
        }
        
        $modelServices = new SeService();
        $services = $modelServices->getForList($id_space);

        foreach ($items['services'] as $s) {
            if( ! in_array($s, $services["ids"])) {
                $services["ids"][] = $s;
                $services["names"][] = '[!] '. $modelServices->getName($id_space, $s, true);
            }
        }

        $formAddName = "orderEditForm";
        $formAdd = new FormAdd($this->request, $formAddName);
        $formAdd->addSelect("services", ServicesTranslator::services($lang), $services["names"], $services["ids"], $items["services"]);
        $formAdd->addFloat("quantities", ServicesTranslator::Quantity($lang), $items["quantities"]);
        $formAdd->addLabel("type", $items["quantity_types"]);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form->addSeparator(ServicesTranslator::Services_list($lang));
        $form->setFormAdd($formAdd);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesorderedit/" . $id_space . "/" . $id);


        if ($form->check()) {
            $id_order = $this->validateEditQuery($id_space, $id, $this->request);
            if($id_order) {
                return $this->redirect("servicesorders/" . $id_space, [], ['order' => ['id' => $id_order]]);
            }
        }

        return $this->render(array(
            "id_space" => $id_space,
            "formAddName" => $formAddName,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
            "data" => ['order' => $value, 'items' => $items]
        ));
    }

    protected function validateEditQuery($id_space, $id, $request) {
        $lang = $this->getLanguage();
        $modelOrder = new SeOrder();
        $modelServices = new SeService();
        $id_user = $this->request->getParameter("id_user") == "" ? "0" : $this->request->getParameter("id_user");
        $id_order = $modelOrder->setOrder(
            $id,
            $id_space,
            $id_user,
            $request->getParameter("id_client"),
            $request->getParameter("no_identification"),
            $_SESSION["id_user"], 
            CoreTranslator::dateToEn($this->request->getParameter("date_open"), $lang), 
            date("Y-m-d", time()), 
            CoreTranslator::dateToEn($this->request->getParameter("date_close"), $lang)
        );
        
        $modelOrder->setModifiedBy($id_space, $id, $_SESSION["id_user"]);
        
        $servicesIds = $this->request->getParameter("services");
        $servicesQuantities = $this->request->getParameter("quantities");


        $filteredServices = [];
        $newIds = [];
        // if a service is defined on multiple lines, combine them and add quantities
        // ignore empty or 0 quantities
        for ($i = 0; $i < count($servicesIds); $i++) {
            $sid = $servicesIds[$i];
            $sq = $servicesQuantities[$i];
            if($sq == 0 || $sq == '') {
                continue;
            }
            if(! isset($filteredServices[$sid])) {
                $filteredServices[$sid] = 0;
            }
            $filteredServices[$sid] += $sq;
        }
        foreach ($filteredServices as $key => $value) {
            if($value > 0) {
                $newIds[] = $key;
            }
        }

        // check for removed services
        $oldServicesIds = $modelOrder->getOrderServices($id_space ,$id)['services'];
        $deletedServicesIds = array_diff($oldServicesIds, $newIds);
        if (!empty($deletedServicesIds)) {
            Configuration::getLogger()->debug('[services][orders] remove services from order', ['order' => $id, 'services' => $deletedServicesIds]);
            // delete removed order services
            foreach($deletedServicesIds as $deletedServiceId) {
                $modelOrder->deleteOrderService($id_space, $deletedServiceId, $id);
            }
        }

        // update service quantities and order service
        foreach ($filteredServices as $sid => $quantity) {
            $qOld = !$id ? 0 : $modelOrder->getOrderServiceQuantity($id_space ,$id, $sid);
            $qDelta = floatval($quantity) - floatval($qOld);
            $modelServices->editquantity($id_space, $sid, $qDelta, "subtract");
            Configuration::getLogger()->debug('[services][orders] set service quantities', ['order' => $id, 'service' => $sid, 'quantity' => $quantity]);
            $modelOrder->setService($id_space, $id_order, $sid, $quantity);
        }

        return $id_order;
    }
}
