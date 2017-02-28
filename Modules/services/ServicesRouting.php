<?php

require_once 'Framework/Routing.php';

class ServicesRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("servicesconfig", "servicesconfig", "servicesconfig", "index", array("id_space"), array(""));
        $this->addRoute("servicesconfigadmin", "servicesconfigadmin", "servicesconfigadmin", "index");
        
        
        // add here the module routes
        $this->addRoute("services", "services", "services", "index", array("id_space"), array(""));
        $this->addRoute("serviceslisting", "serviceslisting", "services", "listing", array("id_space"), array(""));
        $this->addRoute("servicesedit", "servicesedit", "services", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesdelete", "servicesdelete", "services", "delete", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprices", "servicesprices", "servicesprices", "index", array("id_space"), array(""));
        $this->addRoute("servicespriceseditquery", "servicespriceseditquery", "servicesprices", "editquery", array("id_space"), array(""));
        
        
        
        // stock
        $this->addRoute("servicesstock", "servicesstock", "services", "stock", array("id_space"), array(""));
        
        // purchase
        $this->addRoute("servicespurchase", "servicespurchase", "servicespurchase", "index", array("id_space"), array(""));
        $this->addRoute("servicespurchaseedit", "servicespurchaseedit", "servicespurchase", "edit", array("id_space", "id"), array("", ""));
        
        
        // orders
        $this->addRoute("servicesorders", "servicesorders", "servicesorders", "index", array("id_space"), array(""));
        $this->addRoute("servicesordersopened", "servicesordersopened", "servicesorders", "opened", array("id_space"), array(""));
        $this->addRoute("servicesordersclosed", "servicesordersclosed", "servicesorders", "closed", array("id_space"), array(""));
        $this->addRoute("servicesordersall", "servicesordersall", "servicesorders", "all", array("id_space"), array(""));
        $this->addRoute("servicesorderedit", "servicesorderedit", "servicesorders", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesorderdelete", "servicesorderdelete", "servicesorders", "delete", array("id_space", "id"), array("", ""));
        
        // projects
        $this->addRoute("servicesprojects", "servicesprojects", "servicesprojects", "index", array("id_space", "year"), array("", ""));
        $this->addRoute("servicesprojectsopened", "servicesprojectsopened", "servicesprojects", "opened", array("id_space", "year"), array("", ""));
        $this->addRoute("servicesprojectsclosed", "servicesprojectsclosed", "servicesprojects", "closed", array("id_space", "year"), array("", ""));
        $this->addRoute("servicesprojectsall", "servicesprojectsall", "servicesprojects", "all", array("id_space", "year"), array("", ""));
        $this->addRoute("servicesprojectedit", "servicesprojectedit", "servicesprojects", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectdelete", "servicesprojectdelete", "servicesprojects", "delete", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectexport", "servicesprojectexport", "servicesprojects", "export", array("id_space", "id"), array("", ""));
        
        
        
        $this->addRoute("servicesprojectsheet", "servicesprojectsheet", "servicesprojects", "sheet", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectfollowup", "servicesprojectfollowup", "servicesprojects", "followup", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojecteditentry", "servicesprojecteditentry", "servicesprojects", "editentry", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojecteditentryquery", "servicesprojecteditentryquery", "servicesprojects", "editentryquery", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectdeleteentry", "servicesprojectdeleteentry", "servicesprojects", "deleteentry", array("id_space", "id_project", "id"), array("", "", ""));
        
        
        $this->addRoute("servicesgetprojectentry", "servicesgetprojectentry", "servicesproject", "getprojectentry", array("id"), array(""), true);
        $this->addRoute("servicesgetprices", "servicesgetprices", "servicesprices", "getprices", array("id_space", "id_service"), array("", ""), true);
                         
        
        
        // stats
        $this->addRoute("servicesbalance", "servicesbalance", "servicesbalance", "index", array("id_space"), array(""));
        
        // invoicing
        $this->addRoute("servicesinvoiceorder", "servicesinvoiceorder", "servicesinvoiceorder", "index", array("id_space"), array(""));
        $this->addRoute("servicesinvoiceorderedit", "servicesinvoiceorderedit", "servicesinvoiceorder", "edit", array("id_space", "id_invoice", "pdf"), array("", "", ""));
        
        $this->addRoute("servicesinvoiceproject", "servicesinvoiceproject", "servicesinvoiceproject", "index", array("id_space"), array(""));
        $this->addRoute("servicesinvoiceprojectquery", "servicesinvoiceprojectquery", "servicesinvoiceproject", "invoiceproject", array("id_space", "id_project"), array("",""));
        
        
        $this->addRoute("servicesinvoiceprojectedit", "servicesinvoiceprojectedit", "servicesinvoiceproject", "edit", array("id_space", "id_invoice", "pdf"), array("", "", ""));
        
        // statistics
        $this->addRoute("servicesstatisticsorder", "servicesstatisticsorder", "servicesstatisticsorder", "index", array("id_space"), array(""));
        $this->addRoute("servicesstatisticsproject", "servicesstatisticsproject", "servicesstatisticsproject", "index", array("id_space"), array(""));
 
    }
}
