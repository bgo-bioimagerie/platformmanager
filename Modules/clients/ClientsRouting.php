<?php

require_once 'Framework/Routing.php';

class ClientsRouting extends Routing{

    public function routes($router) {
        $router->map('GET|POST', '/clientusersgetuserclients', 'clients/clientsusers/getuserclients', 'clients_getuserclients');
    }
    
    public function listRoutes(){
        
        // config
        $this->addRoute("clientsconfig", "clientsconfig", "clientsconfig", "index", array("id_space"), array(""));

        // clients
        $this->addRoute("clients", "clients", "clientslist", "index", array("id_space"), array(""));
        
        $this->addRoute("clclients", "clclients", "clientslist", "index", array("id_space"), array(""));
        $this->addRoute("clclientedit", "clclientedit", "clientslist", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("clclienteditdelivery", "clclienteditdelivery", "clientslist", "editdeliveryaddress", array("id_space", "id"), array("", ""));
        $this->addRoute("clclienteditinvoice", "clclienteditinvoice", "clientslist", "editinvoiceaddress", array("id_space", "id"), array("", ""));
        $this->addRoute("clclientedit", "clclientedit", "clientslist", "edit", array("id_space", "id"), array("", ""));
        
        $this->addRoute("clclientdelete", "clclientdelete", "clientslist", "delete", array("id_space", "id"), array("", ""));
        
        // clients user
        $this->addRoute("clclientusers", "clclientusers", "clientsusers", "index", array("id_space", "id_client"), array("", ""));
        $this->addRoute("clclientuserdelete", "clclientuserdelete", "clientsusers", "delete", array("id_space", "id_client", "id_user"), array("", "", ""));
        
        // client account
        $this->addRoute("clientsuseraccounts", "clientsuseraccounts", "clientsuseraccounts", "index", array("id_space", "id_user"), array("", ""));
        $this->addRoute("clientsuseraccountsdelete", "clientsuseraccountsdelete", "clientsuseraccounts", "delete", array("id_space", "id_user", "id"), array("", "", ""));
        
        
        
        
        // pricings
        $this->addRoute("clpricings", "clpricings", "clientspricings", "index", array("id_space"), array(""));
        $this->addRoute("clpricingedit", "clpricingedit", "clientspricings", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("clpricingdelete", "clpricingdelete", "clientspricings", "delete", array("id_space", "id"), array("", ""));
        
        // company
        $this->addRoute("clcompany", "clcompany", "clientscompany", "index", array("id_space"), array(""));
        
    }
}
