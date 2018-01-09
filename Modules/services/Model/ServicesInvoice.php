<?php

require_once 'Modules/invoices/Model/InvoiceModel.php';
require_once 'Modules/services/Model/SePrice.php';
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/ServicesTranslator.php';



require_once 'Modules/ecosystem/Model/EcUnit.php';

/**
 * Class defining the SyColorCode model
 *
 * @author Sylvain Prigent
 */
class ServicesInvoice extends InvoiceModel {

    
    public function hasActivity($id_space, $beginPeriod, $endPeriod, $id_resp){
        
        // projects
        $sqlps = "SELECT * FROM se_project WHERE id_space=? AND id_resp=? AND (date_close=? OR date_close='')";
        $projects = $this->runRequest($sqlps, array($id_space, $id_resp, '0000-00-00'))->fetchAll();
        foreach ($projects as $p){
            
            $sqlpd = "SELECT * FROM se_project_service WHERE id_project=? AND id_invoice=0";
            $req = $this->runRequest($sqlpd, array($p["id"]));
            if ($req->rowCount() > 0){
                echo "found a project service for " . $id_resp . "<br/>";
                return true;
            }
        }
        
        // orders
        $modelOrder = new SeOrder();
        $orders = $modelOrder->openedForRespPeriod($beginPeriod, $endPeriod, $id_resp, $id_space);
        if (count($orders) > 0){
            echo "found an opened order for " . $id_resp . "<br/>";
            return true;
        }
        return false;
    }
    
    public function invoice($id_space, $beginPeriod, $endPeriod, $id_unit, $id_resp, $invoice_id, $lang) {

        // get all services
        $sql = "SELECT * FROM se_services WHERE id_space=?";
        $services = $this->runRequest($sql, array($id_space))->fetchAll();

        // get all purchase
        $modelOrder = new SeOrder();
        $orders = $modelOrder->openedForRespPeriod($beginPeriod, $endPeriod, $id_resp, $id_space);

        // get all projects
        $sqlps = "SELECT * FROM se_project WHERE id_resp=? AND (date_close=? OR date_close='')";
        $projects = $this->runRequest($sqlps, array($id_resp, '0000-00-00'))->fetchAll();

        $modelPrice = new SePrice();

        $modelUnit = new EcUnit();
        $id_belongings = $modelUnit->getBelonging($id_unit, $id_space);

        // get quantities
        $content = array();
        $content["count"] = array();
        $content["total_ht"] = 0;
        foreach ($services as $service) {

            // get price
            $unit_price = $modelPrice->getPrice($service["id"], $id_belongings);

            // init
            $quantity = 0;

            // get all the orders services
            foreach ($orders as $order) {

                $sqlo = "SELECT quantity FROM se_order_service WHERE id_order=? AND id_service=?";
                $quantities = $this->runRequest($sqlo, array($order["id"], $service["id"]))->fetchAll();
                foreach ($quantities as $q) {
                    $quantity += $q["quantity"];
                }
            }

            // get all the projects services
            foreach ($projects as $project) {

                // get quantity
                $sqlp = "SELECT quantity FROM se_project_service WHERE id_invoice=0 AND date>=? AND date<=? AND id_service=? AND id_project=?";
                $quantities = $this->runRequest($sqlp, array($beginPeriod, $endPeriod, $service["id"], $project["id"]))->fetchAll();
                //echo "services for project " . $project["id"] . "<br/>";
                //print_r($quantities);
                foreach ($quantities as $q) {
                    $quantity += $q["quantity"];
                }
                // update the invoice id
                $sqlpu = "UPDATE se_project_service SET id_invoice=? WHERE id_invoice=0 AND date>=? AND date<=? AND id_service=? AND id_project=?";
                $this->runRequest($sqlpu, array($invoice_id, $beginPeriod, $endPeriod, $service["id"], $project["id"]));
            }

            // add to content
            if ($quantity > 0){
                $content["count"][] = array("label" => $service["name"], "quantity" => $quantity, "unitprice" => $unit_price);
                $content["total_ht"] += $quantity * $unit_price;
            }
        }

        // close orders
        foreach ($orders as $order) {
            $sqloc = "UPDATE se_order SET id_invoice=?, id_status=0, date_close=? WHERE id=?";
            $this->runRequest($sqloc, array($invoice_id, date("Y-m-d"), $order["id"]));
        }

        return $content;
    }

    public function details($id_space, $invoice_id, $lang) {

        // services
        $sqls = "SELECT * FROM se_services WHERE id_space=?";
        $services = $this->runRequest($sqls, array($id_space))->fetchAll();


        $modelProject = new SeProject();

        // orders
        $sqlo = "SELECT id, no_identification FROM se_order WHERE id_invoice=?";
        $orders = $this->runRequest($sqlo, array($invoice_id))->fetchAll();

        $data = array();
        $data["title"] = ServicesTranslator::Services($lang);
        $data["header"] = array(
            "label" => ServicesTranslator::Service($lang),
            "origin" => ServicesTranslator::Project() . "/" . ServicesTranslator::Orders($lang),
            "quantity" => ServicesTranslator::Quantity($lang)
        );
        $data["content"] = array();
        foreach ($services as $service) {

            // each project
            //echo "sercah in projects <br/>";
            $sqlp = "SELECT DISTINCT id_project FROM se_project_service WHERE id_invoice=? AND id_service=?";
            $projects = $this->runRequest($sqlp, array($invoice_id, $service["id"]))->fetchAll();
            foreach ($projects as $project) {

                $sqlsp = "SELECT * FROM se_project_service WHERE id_project=? AND id_invoice=? AND id_service=?";
                $reqsp = $this->runRequest($sqlsp, array($project[0], $invoice_id, $service["id"]));
                if ($reqsp->rowCount() > 0) {
                    $datap = $reqsp->fetchAll();
                    $quantity = 0;
                    foreach ($datap as $d) {
                        $quantity += $d["quantity"];
                    }
                    $data["content"][] = array(
                        "label" => $service["name"],
                        "origin" => $modelProject->getName($project[0]),
                        "quantity" => $quantity
                    );
                }
            }

            // each order
            // echo "sercah in orders <br/>";
            foreach ($orders as $order) {

                $sqlso = "SELECT * FROM se_order_service WHERE id_service=? AND id_order=?";
                $reqso = $this->runRequest($sqlso, array($service["id"], $order["id"]));
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

    public function delete($id_invoice) {

        $this->deleteProjects($id_invoice);
        $this->deleteOrders($id_invoice);
    }

    // ////////////////////////////////////////////////////////////////////// //
    //                        internal methods
    // ////////////////////////////////////////////////////////////////////// //    
    public function deleteProjects($id_invoice) {
        $sql = "UPDATE se_project_service SET id_invoice=0 WHERE id_invoice=?";
        $this->runRequest($sql, array($id_invoice));
    }

    public function deleteOrders($id_invoice) {
        $sql = "UPDATE se_order SET id_invoice=0, id_status=1, date_close=? WHERE id_invoice=?";
        $this->runRequest($sql, array("0000-00-00", $id_invoice));
    }

}
