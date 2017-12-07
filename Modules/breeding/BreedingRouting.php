<?php

require_once 'Framework/Routing.php';

class BreedingRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("breedingconfig", "breedingconfig", "breedingconfig", "index", array("id_space"), array(""));

        // add here the module routes
        $this->addRoute("breeding", "breeding", "breedingbatchs", "index", array("id_space"), array(""));
        
        // products
        $this->addRoute("brproducts", "brproducts", "breedingproducts", "index", array("id_space"), array(""));
        $this->addRoute("brproductedit", "brproductedit", "breedingproducts", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brproductdelete", "brproductdelete", "breedingproducts", "delete", array("id_space", "id"), array("", ""));
        
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
        
        
        // batch
        $this->addRoute("brbatchs", "brbatchs", "breedingbatchs", "index", array("id_space"), array(""));
        $this->addRoute("brbatchnew", "brbatchnew", "breedingbatchs", "new", array("id_space"), array(""));
        $this->addRoute("brbatchsinprogress", "brbatchsinprogress", "breedingbatchs", "inprogress", array("id_space"), array(""));
        $this->addRoute("brbatchsarchives", "brbatchsarchives", "breedingbatchs", "archives", array("id_space"), array(""));
        
        $this->addRoute("brbatch", "brbatch", "breedingbatchs", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("brbatchdelete", "brbatchdelete", "breedingbatchs", "delete", array("id_space", "id"), array("", ""));
        
        
        
        
        // sexing
        $this->addRoute("brsexing", "brsexing", "breedingsexing", "index", array("id_space", "id_batch"), array("", ""));
        
        // import
        
        $this->addRoute("brimport", "brimport", "breedingimport", "index", array("id_space"), array(""));
        
    }
}
