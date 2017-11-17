<?php

require_once 'Framework/Routing.php';

class BreedingRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("breedingconfig", "breedingconfig", "breedingconfig", "index", array("id_space"), array(""));

        // add here the module routes
        $this->addRoute("breeding", "breeding", "breedingbatchs", "index", array("id_space"), array(""));
        
        // clients
        $this->addRoute("brclients", "brclients", "breedingclients", "index", array("id_space"), array(""));
        $this->addRoute("brclientedit", "brclientedit", "breedingclients", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brclientdelete", "brclientdelete", "breedingclients", "delete", array("id_space", "id"), array("", ""));
        
        // pricings
        $this->addRoute("brpricings", "brpricings", "breedingpricings", "index", array("id_space"), array(""));
        $this->addRoute("brpricingedit", "brpricingedit", "breedingpricings", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brpricingdelete", "brpricingdelete", "breedingpricings", "delete", array("id_space", "id"), array("", ""));
        
        // categories
        $this->addRoute("brproductcategories", "brproductcategories", "breedingcategories", "index", array("id_space"), array(""));
        $this->addRoute("brproductcategoryedit", "brproductcategoryedit", "breedingcategories", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brproductcategorydelete", "brproductcategorydelete", "breedingcategories", "delete", array("id_space", "id"), array("", ""));
        
        // losse types
        $this->addRoute("brlossetypes", "brlossetypes", "breedinglossetypes", "index", array("id_space"), array(""));
        $this->addRoute("brlossetypeedit", "brlossetypeedit", "breedinglossetypes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brlossetypedelete", "brlossetypedelete", "breedinglossetypes", "delete", array("id_space", "id"), array("", ""));
        

        // losses
        $this->addRoute("brlosses", "brlosses", "breedinglosses", "index", array("id_space", "id_batch"), array("", ""));
        $this->addRoute("brlosseedit", "brlosseedit", "breedinglosses", "edit", array("id_space", "id_batch", "id"), array("", "", ""));
        $this->addRoute("brlossedelete", "brlossedelete", "breedinglosses", "delete", array("id_space", "id_batch", "id"), array("", "", ""));
        
        // moves
        $this->addRoute("brmoves", "brmoves", "breedingmoves", "index", array("id_space", "id_batch"), array("", ""));
        $this->addRoute("brmoveedit", "brmoveedit", "breedingmoves", "edit", array("id_space", "id_batch", "id"), array("", "", ""));
        
        
        // treatments
        $this->addRoute("brtreatments", "brtreatments", "breedingtreatments", "index", array("id_space", "id_batch"), array("", ""));
        $this->addRoute("brtreatmentedit", "brtreatmentedit", "breedingtreatments", "edit", array("id_space", "id_batch", "id"), array("", "", ""));
        $this->addRoute("brtreatmentdelete", "brtreatmentdelete", "breedingtreatments", "delete", array("id_space", "id_batch", "id"), array("", "", ""));
        
        // chipping
        $this->addRoute("brchipping", "brchipping", "breedingchipping", "index", array("id_space", "id_batch"), array("", ""));
        $this->addRoute("brchippingedit", "brchippingedit", "breedingchipping", "edit", array("id_space", "id_batch", "id"), array("", "", ""));
        $this->addRoute("brchippingdelete", "brchippingdelete", "breedingchipping", "delete", array("id_space", "id_batch", "id"), array("", "", ""));
        
        
        // Products
        $this->addRoute("brproducts", "brproducts", "breedingproducts", "index", array("id_space"), array(""));
        $this->addRoute("brproductedit", "brproductedit", "breedingproducts", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brproductdelete", "brproductdelete", "breedingproducts", "delete", array("id_space", "id"), array("", ""));
        
        $this->addRoute("brproductstageedit", "brproductstageedit", "breedingproducts", "stageedit", array("id_space", "id_product", "id"), array("", "", ""));
        $this->addRoute("brproductstagedelete", "brproductstagedelete", "breedingproducts", "stagedelete", array("id_space", "id_product", "id"), array("", "", ""));
        
        
        // batch
        $this->addRoute("brbatchs", "brbatchs", "breedingbatchs", "index", array("id_space"), array(""));
        $this->addRoute("brbatchnew", "brbatchnew", "breedingbatchs", "new", array("id_space"), array(""));
        $this->addRoute("brbatchsinprogress", "brbatchsinprogress", "breedingbatchs", "inprogress", array("id_space"), array(""));
        $this->addRoute("brbatchsarchives", "brbatchsarchives", "breedingbatchs", "archives", array("id_space"), array(""));
        
        $this->addRoute("brbatch", "brbatch", "breedingbatchs", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brbatchdelete", "brbatchdelete", "breedingbatchs", "delete", array("id_space", "id"), array("", ""));
        
        // delivery methods
        $this->addRoute("brdeliveries", "brdeliveries", "breedingdelivery", "index", array("id_space"), array(""));
        $this->addRoute("brdeliveryedit", "brdeliveryedit", "breedingdelivery", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brdeliverydelete", "brdeliverydelete", "breedingdelivery", "delete", array("id_space", "id"), array("", ""));
        
        
        // sale
        $this->addRoute("brsalenew", "brsalenew", "breedingsales", "new", array("id_space"), array(""));
        $this->addRoute("brsaleedit", "brsaleedit", "breedingsales", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brsaleitems", "brsaleitems", "breedingsales", "items", array("id_space", "id_sale"), array("", ""));
        $this->addRoute("brsaleitemedit", "brsaleitemedit", "breedingsales", "itemedit", array("id_space", "id_sale", "id"), array("", "", ""));
    
        
        $this->addRoute("brsalesinprigress", "brsalesinprigress", "breedingsales", "inprogress", array("id_space"), array(""));
        $this->addRoute("brsalessent", "brsalessent", "breedingsales", "sent", array("id_space"), array(""));
        $this->addRoute("brsalescanceled", "brsalescanceled", "breedingsales", "canceled", array("id_space"), array(""));
       
        // prices 
        $this->addRoute("brprices", "brprices", "breedingprices", "index", array("id_space"), array(""));
        $this->addRoute("brpriceedit", "brpriceedit", "breedingprices", "edit", array("id_space", "id_product_stage"), array("", ""));
        
        // company 
        $this->addRoute("brcompany", "brcompany", "breedingcompany", "index", array("id_space"), array(""));
        
        // contact types
        $this->addRoute("brcontacttypes", "brcontacttypes", "breedingcontacttypes", "index", array("id_space"), array(""));
        $this->addRoute("brcontacttypeedit", "brcontacttypeedit", "breedingcontacttypes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brcontacttypedelete", "brcontacttypedelete", "breedingcontacttypes", "delete", array("id_space", "id"), array("", ""));
        
        // company
        $this->addRoute("brcompany", "brcompany", "breedingcompany", "index", array("id_space"), array(""));
        
    }
}
