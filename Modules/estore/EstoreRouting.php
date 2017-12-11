<?php

require_once 'Framework/Routing.php';

class EstoreRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("estoreconfig", "estoreconfig", "estoreconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("estore", "estore", "estorecatalog", "index", array("id_space", "id_category"), array("", ""));
        
        // ...
        $this->addRoute("estorecatalog", "estorecatalog", "estorecatalog", "index", array("id_space", "id_category"), array("", ""));
        
        // products
        $this->addRoute("esproductcategories", "esproductcategories", "estoreproductcategory", "index", array("id_space"), array(""));
        $this->addRoute("esproductcategoryedit", "esproductcategoryedit", "estoreproductcategory", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("esproductcategorydelete", "esproductcategorydelete", "estoreproductcategory", "delete", array("id_space", "id"), array("", ""));
        
        
        $this->addRoute("esproducts", "esproducts", "estoreproduct", "index", array("id_space"), array(""));
        $this->addRoute("esproductedit", "esproductedit", "estoreproduct", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("esproductdelete", "esproductdelete", "estoreproduct", "delete", array("id_space", "id"), array("", ""));
        
        // contact types
        $this->addRoute("escontacttypes", "escontacttypes", "estorecontacttypes", "index", array("id_space"), array(""));
        $this->addRoute("escontacttypeedit", "escontacttypeedit", "estorecontacttypes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("escontacttypedelete", "escontacttypedelete", "estorecontacttypes", "delete", array("id_space", "id"), array("", ""));
        
        // delivery methods
        $this->addRoute("esdeliveries", "esdeliveries", "estoredelivery", "index", array("id_space"), array(""));
        $this->addRoute("esdeliveryedit", "esdeliveryedit", "estoredelivery", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("esdeliverydelete", "esdeliverydelete", "estoredelivery", "delete", array("id_space", "id"), array("", ""));
        
        // prices
        $this->addRoute("esprices", "esprices", "estoreprice", "index", array("id_space"), array(""));
        $this->addRoute("espriceedit", "espriceedit", "estoreprice", "edit", array("id_space", "id"), array("", ""));
        
        
        
        // sales
        $this->addRoute("essalenew", "essalenew", "estoresale", "new", array("id_space"), array(""));
        
        $this->addRoute("essaleentered", "essaleentered", "estoresale", "entered", array("id_space"), array(""));
        $this->addRoute("essaleenterededit", "essaleenterededit", "estoresale", "enterededit", array("id_space", "id"), array("", ""));
        $this->addRoute("essaleenteredadminedit", "essaleenteredadminedit", "estoresale", "enteredadminedit", array("id_space", "id"), array("", ""));
        
        $this->addRoute("essaleinprogress", "essaleinprogress", "estoresale", "inprogress", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("esalequote", "esalequote", "estoresale", "quote", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("esaledelivery", "esaledelivery", "estoresale", "delivery", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("esaledeliverypdf", "esaledeliverypdf", "estoresale", "deliverypdf", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("esaleinvoice", "esaleinvoice", "estoresale", "invoice", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("esaleinvoicepdf", "esaleinvoicepdf", "estoresale", "invoicepdf", array("id_space", "id_sale"), array("", ""));
        
        
       
        $this->addRoute("esalesinprogress", "esalesinprogress", "estoresale", "inprogresslist", array("id_space"), array(""));
        $this->addRoute("esalesquoted", "esalesquoted", "estoresale", "quotedlist", array("id_space"), array(""));
        $this->addRoute("esalessent", "esalessent", "estoresale", "sentlist", array("id_space"), array(""));
        $this->addRoute("esalescanceled", "esalescanceled", "estoresale", "canceledlist", array("id_space"), array(""));
        $this->addRoute("esalesarchive", "esalesarchive", "estoresale", "archivelist", array("id_space"), array(""));
        
        
        
    }
}
