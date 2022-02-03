<?php

require_once 'Framework/Routing.php';

class ServicesRouting extends Routing {

    public function routes($router) {
        $router->map('GET', '/user/services/projects/[i:id_space]', 'services/servicesprojects/user', 'services_user_projects');
        $router->map('GET|POST', '/services/getServiceType/[i:id_space]/[i:id_service]', 'services/services/getServiceType', 'services_getservicetype');
    }

    public function listRoutes() {

        // config
        $this->addRoute("servicesconfig", "servicesconfig", "servicesconfig", "index", array("id_space"), array(""));
        // $this->addRoute("servicesconfigadmin", "servicesconfigadmin", "servicesconfigadmin", "index");

        // add here the module routes
        $this->addRoute("services", "services", "services", "index", array("id_space"), array(""));
        $this->addRoute("serviceslisting", "serviceslisting", "serviceslisting", "listing", array("id_space"), array(""));
        $this->addRoute("servicesedit", "servicesedit", "serviceslisting", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesdelete", "servicesdelete", "serviceslisting", "delete", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprices", "servicesprices", "servicesprices", "index", array("id_space"), array(""));
        $this->addRoute("servicespriceseditquery", "servicespriceseditquery", "servicesprices", "editquery", array("id_space"), array(""));


        // stock
        $this->addRoute("servicesstock", "servicesstock", "serviceslisting", "stock", array("id_space"), array(""));

        // purchase
        $this->addRoute("servicespurchase", "servicespurchase", "servicespurchase", "index", array("id_space"), array(""));
        $this->addRoute("servicespurchaseedit", "servicespurchaseedit", "servicespurchase", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("servicespurchasedelete", "servicespurchasedelete", "servicespurchase", "delete", array("id_space", "id"), array("", ""));

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
        $this->addRoute("servicesprojectsperiod", "servicesprojectsperiod", "servicesprojects", "period", array("id_space", "year"), array("", ""));



        $this->addRoute("servicesprojectsall", "servicesprojectsall", "servicesprojects", "all", array("id_space", "year"), array("", ""));
        $this->addRoute("servicesprojectedit", "servicesprojectedit", "servicesprojects", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectdelete", "servicesprojectdelete", "servicesprojects", "delete", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectexport", "servicesprojectexport", "servicesprojects", "export", array("id_space", "id"), array("", ""));

        $this->addRoute("servicesorigins", "servicesorigins", "servicesorigins", "index", array("id_space"), array(""));
        $this->addRoute("serviceoriginedit", "serviceoriginedit", "servicesorigins", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("serviceorigindelete", "serviceorigindelete", "servicesorigins", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("servicesvisas", "servicesvisas", "servicesvisa", "index", array("id_space"), array(""));
        $this->addRoute("servicesvisaedit", "servicesvisaedit", "servicesvisa", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesvisadelete", "servicesvisadelete", "servicesvisa", "delete", array("id_space", "id"), array("", ""));


        $this->addRoute("servicesprojectsheet", "servicesprojectsheet", "servicesprojects", "sheet", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectfollowup", "servicesprojectfollowup", "servicesprojects", "followup", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectsclosing", "servicesprojectclosing", "servicesprojects", "closing", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojectsample", "servicesprojectsample", "servicesprojects", "samplestock", array("id_space", "id"), array("", ""));
        //@deprecated
        $this->addRoute("servicesprojecteditentry", "servicesprojecteditentry", "servicesprojects", "editentry", array("id_space", "id"), array("", ""));
        $this->addRoute("servicesprojecteditentryquery", "servicesprojecteditentryquery", "servicesprojects", "editentryquery", array("id_space"), array(""));
        $this->addRoute("servicesprojectdeleteentry", "servicesprojectdeleteentry", "servicesprojects", "deleteentry", array("id_space", "id_project", "id"), array("", "", ""));

        $this->addRoute("servicesprojectgantt", "servicesprojectgantt", "servicesprojectgantt", "index", array("id_space", "allPeriod", "incharge"), array("", "", ""));



        $this->addRoute("servicesgetprojectentry", "servicesgetprojectentry", "servicesproject", "getprojectentry", array("id_space", "id"), array("", ""), true);
        $this->addRoute("servicesgetprices", "servicesgetprices", "servicesprices", "getprices", array("id_space", "id_service"), array("", ""), true);



        // stats
        // deprecated ?
        $this->addRoute("servicesbalance", "servicesbalance", "servicesbalance", "index", array("id_space"), array(""));

        // invoicing
        $this->addRoute("servicesinvoiceorder", "servicesinvoiceorder", "servicesinvoiceorder", "index", array("id_space"), array(""));
        $this->addRoute("servicesinvoiceorderedit", "servicesinvoiceorderedit", "servicesinvoiceorder", "edit", array("id_space", "id_invoice", "pdf"), array("", "", ""));

        $this->addRoute("servicesinvoiceproject", "servicesinvoiceproject", "servicesinvoiceproject", "index", array("id_space"), array(""));
        $this->addRoute("servicesinvoiceprojectquery", "servicesinvoiceprojectquery", "servicesinvoiceproject", "invoiceproject", array("id_space", "id_project"), array("", ""));


        $this->addRoute("servicesinvoiceprojectedit", "servicesinvoiceprojectedit", "servicesinvoiceproject", "edit", array("id_space", "id_invoice", "pdf"), array("", "", ""));

        // statistics
        $this->addRoute("servicesstatisticsorder", "servicesstatisticsorder", "servicesstatisticsorder", "index", array("id_space"), array(""));
        $this->addRoute("servicesstatisticsproject", "servicesstatisticsproject", "servicesstatisticsproject", "index", array("id_space"), array(""));
        $this->addRoute("servicesstatisticsmailresps", "servicesstatisticsmailresps", "servicesstatisticsproject", "mailresps", array("id_space"), array(""));

        $this->addRoute("servicesstatisticsprojectsamplesreturn", "servicesstatisticsprojectsamplesreturn", "servicesstatisticsproject", "samplesreturn", array("id_space"), array(""));

        
        // stock
        $this->addRoute("stockcabinets", "stockcabinets", "stockcabinet", "index", array("id_space"), array(""));
        $this->addRoute("stockcabinetedit", "stockcabinetedit", "stockcabinet", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("stockcabinetdelete", "stockcabinetdelete", "stockcabinet", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("stockshelfs", "stockshelfs", "stockshelf", "index", array("id_space"), array(""));
        $this->addRoute("stockshelfedit", "stockshelfedit", "stockshelf", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("stockshelfdelete", "stockshelfdelete", "stockshelf", "delete", array("id_space", "id"), array("", ""));
        
        
    }

}
