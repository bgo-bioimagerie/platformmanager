<?php

require_once 'Framework/Routing.php';

class EstoreRouting extends Routing{
    
    public function listRoutes(){
        
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
        
         // contact types
        $this->addRoute("escancelreasons", "escancelreasons", "estorecancelreasons", "index", array("id_space"), array(""));
        $this->addRoute("escancelreasonedit", "escancelreasonedit", "estorecancelreasons", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("escancelreasondelete", "escancelreasondelete", "estorecancelreasons", "delete", array("id_space", "id"), array("", ""));
        
        // not feasible reason
        $this->addRoute("esnotfeasiblereasons", "esnotfeasiblereasons", "estorenotfeasiblereasons", "index", array("id_space"), array(""));
        $this->addRoute("esnotfeasiblereasonsedit", "esnotfeasiblereasonsedit", "estorenotfeasiblereasons", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("esnotfeasiblereasonsdelete", "esnotfeasiblereasonsdelete", "estorenotfeasiblereasons", "delete", array("id_space", "id"), array("", ""));
        
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
        
        
        
        $this->addRoute("essalefeasibility", "essalefeasibility", "estoresale", "feasibility", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essaletodoquote", "essaletodoquote", "estoresale", "todoquote", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essalequotepdf", "essalequotepdf", "estoresale", "todoquotepdf", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essalequotesent", "essalequotesent", "estoresale", "quotesent", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essaletosendsale", "essaletosendsale", "estoresale", "tosendsale", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essaletosendsalepdf", "essaletosendsalepdf", "estoresale", "tosendsalepdf", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essaleinvoicing", "essaleinvoicing", "estoresale", "invoicing", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essaleinvoicingpdf", "essaleinvoicingpdf", "estoresale", "invoicingpdf", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essalepaymentpending", "essalepaymentpending", "estoresale", "paymentpending", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("essaleended", "essaleended", "estoresale", "ended", array("id_space", "id_sale"), array("", ""));
        
        $this->addRoute("essaleenteredadmineditlist", "essaleenteredadmineditlist", "estoresale", "enteredadmineditlist", array("id_space"), array(""));
        $this->addRoute("essalefeasibilitylist", "essalefeasibilitylist", "estoresale", "feasibilitylist", array("id_space"), array(""));
        $this->addRoute("essaletodoquotelist", "essaletodoquotelist", "estoresale", "todoquotelist", array("id_space"), array(""));
        $this->addRoute("essalequotesentlist", "essalequotesentlist", "estoresale", "quotesentlist", array("id_space"), array(""));
        $this->addRoute("essaletosendsalelist", "essaletosendsalelist", "estoresale", "tosendsalelist", array("id_space"), array(""));
        $this->addRoute("essaleinvoicinglist", "essaleinvoicinglist", "estoresale", "invoicinglist", array("id_space"), array(""));
        $this->addRoute("essalepaymentpendinglist", "essalepaymentpendinglist", "estoresale", "paymentpendinglist", array("id_space"), array(""));
        $this->addRoute("essaleendedlist", "essaleendedlist", "estoresale", "endedlist", array("id_space"), array(""));
        
        $this->addRoute("esalescanceled", "esalescanceled", "estoresale", "canceledlist", array("id_space"), array(""));
        $this->addRoute("esalescancel", "esalescancel", "estoresale", "cancel", array("id_space", "id_sale"), array("", ""));
        
        
        $this->addRoute("espurchaseorderdownload", "espurchaseorderdownload", "estoresale", "purchaseorderdownload", array("id_space", "id_sale"), array("", ""));
        
        /*
        
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
        
        */
        
    }
}
