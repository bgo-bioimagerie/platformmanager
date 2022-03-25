<?php

require_once 'Modules/invoices/Model/InvoiceModel.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';

require_once 'Modules/services/Model/SePrice.php';
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/ServicesTranslator.php';

require_once 'Modules/clients/Model/ClPricing.php';

/**
 * Class defining the services invoice model
 *
 * @author Sylvain Prigent
 */
class ServicesInvoice extends InvoiceModel {

    public static string $INVOICES_SERVICES_PROJECTS_CLIENT = 'invoices_services_projects_client';
    public static string $INVOICES_SERVICES_ORDERS_CLIENT = 'invoices_services_orders_client';

    public function hasActivity($id_space, $beginPeriod, $endPeriod, $id_resp){
        
        // projects
        $sqlps = "SELECT * FROM se_project WHERE deleted=0 AND id_space=? AND id_resp=? AND date_close is NULL";
        $projects = $this->runRequest($sqlps, array($id_space, $id_resp))->fetchAll();
        foreach ($projects as $p){
            
            $sqlpd = "SELECT * FROM se_project_service WHERE id_project=? AND id_invoice=0 AND id_space=?";
            $req = $this->runRequest($sqlpd, array($p["id"], $id_space));
            if ($req->rowCount() > 0){
                return true;
            }
        }
        
        // orders
        $modelOrder = new SeOrder();
        $orders = $modelOrder->openedForClientPeriod($beginPeriod, $endPeriod, $id_resp, $id_space);
        if (count($orders) > 0){
            return true;
        }
        return false;
    }

    public function invoiceOrders($id_space, $beginPeriod, $endPeriod, $id_client, $id_user, $lang='en') {
        $modelOrder = new SeOrder();
        $modelInvoice = new InInvoice();
        $modelInvoiceItem = new InInvoiceItem();

        $sim = new ServicesInvoice();
        $contentAll = $sim->getInvoiceOrders($id_space, $beginPeriod, $endPeriod, $id_client);

        if (empty($contentAll)) {
            return false;
        }

        // get the bill number
        $number = $modelInvoice->getNextNumber($id_space);
        $module = "services";
        $controller = "servicesinvoiceorder";
        $id_invoice = $modelInvoice->addInvoice($module, $controller, $id_space, 'in progress', date("Y-m-d", time()), $id_client);
        $modelInvoice->setEditedBy($id_space, $id_invoice, $id_user);
        $modelInvoice->setTitle($id_space, $id_invoice, ServicesTranslator::services($lang).": " . CoreTranslator::dateFromEn($beginPeriod, $lang) . " => " . CoreTranslator::dateFromEn($endPeriod, $lang));

        $total_ht = $contentAll['total_ht'];
        $details = $contentAll['details'];
        $content = $contentAll['content'];
        $orders = $contentAll['orders'];
        $modelInvoiceItem->setItem($id_space, 0, $id_invoice, $module, $controller, $content, $details, $total_ht);
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
        $modelInvoice->setNumber($id_space, $id_invoice, $number);

        foreach ($orders as $order) {
            $modelOrder->setEntryCloded($id_space, $order["id"]);
            $modelOrder->setInvoiceID($id_space ,$order["id"], $id_invoice);
        }
        return true;
    }

    public function getInvoiceOrders($id_space, $beginPeriod, $endPeriod, $id_client) {
        $modelOrder = new SeOrder();
        // select all the opened orders
        $orders = $modelOrder->openedForClientPeriod($beginPeriod, $endPeriod, $id_client, $id_space);

        if (count($orders) == 0) {
            return $orders;
        }
        $services = $modelOrder->openedItemsForClient($id_space, $id_client);
        $modelClPricing = new ClPricing();
        $pricing = $modelClPricing->getPricingByClient($id_space, $id_client)[0];
        $contentServices = $this->parseServicesToContent($id_space, $services, $pricing['id']);
        $content = '';
        foreach ($contentServices as $c) {
            $content .= $c['content'];
        }
        $details = $this->parseOrdersToDetails($id_space, $orders);
        $total_ht = $this->calculateTotal($id_space, $services, $pricing['id']);

        return [
            'total_ht' => $total_ht,
            'content' => $content,
            'details' => $details,
            'orders' => $orders,
            'services' => $contentServices
        ];
    }

    protected function parseOrdersToDetails($id_space, $orders) {
        $details = "";
        foreach ($orders as $order) {
            $details .= $order["no_identification"] . "=servicesorderedit/" . $id_space . "/" . $order["id"] . ";";
        }
        return $details;
    }

    protected function parseServicesToContent($id_space, $services, $id_belonging) {
        $addedServices = array();
        $modelPrice = new SePrice();
        for ($i = 0; $i < count($services); $i++) {
            $quantity = 0;
            if (!in_array($services[$i]["id_service"], $addedServices)) {
                for ($j = $i; $j < count($services); $j++) {
                    if ($services[$j]["id_service"] == $services[$i]["id_service"]) {
                        $quantity += floatval($services[$j]["quantity"]);
                    }
                }
                $price = $modelPrice->getPrice($id_space, $services[$i]["id_service"], $id_belonging);
                $addedServices[] = [
                    'id' => $services[$i]["id_service"],
                    'quantity' => $quantity,
                    'unitprice' => $price,
                    'content' => $services[$i]["id_service"] . "=" . $quantity . "=" . $price . ";"
                ];
            }
        }
        return $addedServices;
    }

    protected function calculateTotal($id_space, $services, $id_belonging) {
        $total_HT = 0;
        $modelPrice = new SePrice();
        foreach ($services as $service) {
            $price = $modelPrice->getPrice($id_space, $service["id_service"], $id_belonging);
            $total_HT += floatval($service["quantity"]) *  floatval($price);
        }
        return $total_HT;
    }

    public function invoiceProjects($id_space, $beginPeriod, $endPeriod, $id_client, $id_user, $id_projects, $lang='en') {
        $modelProject = new SeProject();
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoice = new InInvoice();
        $module = "services";
        $controller = "servicesinvoiceproject";


        $contentAll = $this->getInvoiceProjects($id_space, $id_client, $id_projects);
        if(empty($contentAll['services'])) {
            return false;
        }

        $number = $modelInvoice->getNextNumber($id_space);
        $id_invoice = $modelInvoice->addInvoice($module, $controller, $id_space, 'in progress', date("Y-m-d", time()), $id_client, 0, $beginPeriod, $endPeriod);
        $modelInvoice->setEditedBy($id_space, $id_invoice, $id_user);
        foreach($contentAll['services'] as $s){
            $modelProject->setServiceInvoice($id_space, $s['id'], $id_invoice);
        }

        $total_ht = $contentAll['total_ht'];
        $content = $contentAll['content'];

        $details = "";
        $title = ServicesTranslator::Projects($lang).":";
        foreach ($id_projects as $id_proj) {
            $name = $modelProject->getName($id_space, $id_proj);
            $details .= $name . "=" . "servicesprojectfollowup/" . $id_space . "/" . $id_proj . ";";
            $title .= " ".$name;
        }
        $title = substr($title, 0, 255);
        $modelInvoiceItem->setItem($id_space ,0, $id_invoice, $module, $controller, $content, $details, $total_ht);
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
        $modelInvoice->setTitle($id_space, $id_invoice, $title);
        $modelInvoice->setNumber($id_space, $id_invoice, $number);
        return true;
    }

    public function getInvoiceProjects($id_space, $id_client, $id_projects) {
        // parse content
        $modelClient = new ClClient();
        $id_belonging = $modelClient->getPricingID($id_space ,$id_client);

        $total_ht = 0;
        $modelProject = new SeProject();
        $addedServices = array();
        $addedServicesCount = array();
        $addedServicesPrice = array();
        $addedServicesComment = array();
        $modelPrice = new SePrice();
        $servicesToInvoice = [];
        $serviceList = [];
        foreach ($id_projects as $id_proj) {
            $services = $modelProject->getNoInvoicesServices($id_space, $id_proj);

            $servicesMerged = array();
            for ($i = 0; $i < count($services); $i++) {
                $servicesToInvoice[] = $services[$i]["id"];
                if (!isset($services[$i]["counted"])) {
                    $quantity = floatval($services[$i]["quantity"]);

                    for ($j = $i + 1; $j < count($services); $j++) {
                        if ($services[$i]["comment"] == $services[$j]["comment"] && $services[$i]["id_service"] == $services[$j]["id_service"]) {
                            $quantity += floatval($services[$j]["quantity"]);
                            $services[$j]["counted"] = 1;
                        }
                    }
                    $data["id_service"] = $services[$i]["id_service"];
                    $data["comment"] = $services[$i]["comment"];
                    $data["quantity"] = $quantity;
                    $servicesMerged[] = $data;
                }
            }

            for ($i = 0; $i < count($servicesMerged); $i++) {
                $addedServices[] = $servicesMerged[$i]["id_service"];
                $quantity = floatval($servicesMerged[$i]["quantity"]);
                $price = floatval($modelPrice->getPrice($id_space, $servicesMerged[$i]["id_service"], $id_belonging));
                $addedServicesCount[] = $quantity;
                $addedServicesPrice[] = $price;
                $addedServicesComment[] = $servicesMerged[$i]["comment"];
                $total_ht += $quantity * $price;
                $serviceList[] = [
                    'id' => $servicesMerged[$i]["id_service"],
                    'quantity' => $quantity,
                    'unitprice' => $price,
                    'comment' => $servicesMerged[$i]["comment"]
                ];

            }
        }

        $content = "";
        for ($i = 0; $i < count($serviceList); $i++) {
            $content .= $serviceList[$i]['id'] . "=" . $serviceList[$i]['quantity'] . "=" . $serviceList[$i]['unitprice'] . "=" . $serviceList[$i]['comment'] . ";";
        }
        return ['total_ht' => $total_ht, 'content' => $content, 'services' => $serviceList];
    }
    
    public function invoice($id_space, $beginPeriod, $endPeriod, $id_client, $id_invoice, $lang) {
        $content = array();
        $content["count"] = array();
        $content["total_ht"] = 0;

        $ssm = new SeService();

        // orders
        $ordersContent = $this->getInvoiceOrders($id_space, $beginPeriod, $endPeriod, $id_client);

        $content['total_ht'] += $ordersContent['total_ht'];
        $orders = $ordersContent['orders'];
        $ordersServices = $ordersContent['services'];
        foreach($ordersServices as $orderService) {
            $name = $ssm->getName($id_space, $orderService['id']);
            $content["count"][] = array("id" => $orderService['id'], "label" => $name, "quantity" => $orderService['quantity'], "unitprice" => $orderService['unitprice']);
        }

        // close orders
        $modelOrder = new SeOrder();
        foreach ($orders as $order) {
            $modelOrder->setEntryCloded($id_space, $order["id"]);
            $modelOrder->setInvoiceID($id_space ,$order["id"], $id_invoice);
        }

        // projects
        $modelProject = new SeProject();
        $id_projects = $modelProject->getProjectsOpenedPeriodResp($id_space, $beginPeriod, $endPeriod, $id_client);
        $projectsContent = $this->getInvoiceProjects($id_space, $id_client, $id_projects);

        $content['total_ht'] += $projectsContent['total_ht'];
        $projectServices = $projectsContent['services'];
        foreach($projectServices as $projectService){
            $name = $ssm->getName($id_space, $projectService['id']);
            $content["count"][] = array("id" => $projectService['id'], "label" => $name, "quantity" => $projectService['quantity'], "unitprice" => $projectService['unitprice']);
            $modelProject->setServiceInvoice($id_space, $projectService["id"], $id_invoice);
        }

        return $content;
    }

    public function details($id_space, $invoice_id, $lang) {

        // services
        $sqls = "SELECT * FROM se_services WHERE id_space=? AND deleted=0";
        $services = $this->runRequest($sqls, array($id_space))->fetchAll();


        $modelProject = new SeProject();

        // orders
        $sqlo = "SELECT id, no_identification FROM se_order WHERE id_invoice=? AND id_space=? AND deleted=0";
        $orders = $this->runRequest($sqlo, array($invoice_id, $id_space))->fetchAll();

        $data = array();
        $data["title"] = ServicesTranslator::Services($lang);
        $data["header"] = array(
            "label" => ServicesTranslator::Service($lang),
            "origin" => ServicesTranslator::Project() . "/" . ServicesTranslator::Orders($lang),
            "quantity" => ServicesTranslator::Quantity($lang)
        );
        $data["content"] = array();
        foreach ($services as $service) {
            $sqlp = "SELECT DISTINCT id_project FROM se_project_service WHERE id_invoice=? AND id_service=? AND id_space=? AND deleted=0";
            $projects = $this->runRequest($sqlp, array($invoice_id, $service["id"], $id_space))->fetchAll();
            foreach ($projects as $project) {

                $sqlsp = "SELECT * FROM se_project_service WHERE id_project=? AND id_invoice=? AND id_service=? AND id_space=? AND deleted=0";
                $reqsp = $this->runRequest($sqlsp, array($project[0], $invoice_id, $service["id"], $id_space));
                if ($reqsp->rowCount() > 0) {
                    $datap = $reqsp->fetchAll();
                    $quantity = 0;
                    foreach ($datap as $d) {
                        $quantity += $d["quantity"];
                    }
                    $data["content"][] = array(
                        "label" => $service["name"],
                        "origin" => $modelProject->getName($id_space, $project[0]),
                        "quantity" => $quantity
                    );
                }
            }

            // each order
            foreach ($orders as $order) {

                $sqlso = "SELECT * FROM se_order_service WHERE id_service=? AND id_order=? AND id_space=? AND deleted=0";
                $reqso = $this->runRequest($sqlso, array($service["id"], $order["id"], $id_space));
                if ($reqso->rowCount() > 0) {
                    $datao = $reqso->fetchAll();
                    $quantity = 0;
                    foreach ($datao as $d) {
                        $quantity += $d["quantity"];
                    }
                    $data["content"][] = array(
                        "label" => $service["name"],
                        "origin" => $order["no_identification"],
                        "quantity" => $quantity
                    );
                }
            }
        }
        return $data;
    }

    public function delete($id_space, $id_invoice) {

        $this->deleteProjects($id_space, $id_invoice);
        $this->deleteOrders($id_space, $id_invoice);
    }

    // ////////////////////////////////////////////////////////////////////// //
    //                        internal methods
    // ////////////////////////////////////////////////////////////////////// //    
    public function deleteProjects($id_space, $id_invoice) {
        $sql = "UPDATE se_project_service SET id_invoice=0 WHERE id_invoice=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_invoice, $id_space));
    }

    public function deleteOrders($id_space, $id_invoice) {
        $sql = "UPDATE se_order SET id_invoice=0, id_status=1, date_close=null WHERE id_invoice=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_invoice, $id_space));
    }

}
