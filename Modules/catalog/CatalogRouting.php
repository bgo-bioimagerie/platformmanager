<?php

require_once 'Framework/Routing.php';

class CatalogRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("catalogconfigadmin", "catalogconfigadmin", "catalogconfigadmin", "index");
        $this->addRoute("catalogconfig", "catalogconfig", "catalogconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("catalogsettings", "catalogsettings", "catalogadmin", "index", array("id_space"), array(""));
        
        $this->addRoute("catalog", "catalog", "catalogview", "index", array("id_space", "idCategory"), array("", ""));
        
        $this->addRoute("catalogcategories", "catalogcategories", "catalogadmin", "categories", array("id_space"), array(""));
        $this->addRoute("catalogcategoryedit", "catalogcategoryedit", "catalogadmin", "categoryedit", array("id_space", "id"), array("", ""));
        $this->addRoute("catalogcategorydelete", "catalogcategorydelete", "catalogadmin", "categorydelete", array("id_space", "id"), array("", ""));
        
        $this->addRoute("catalogprestations", "catalogprestations", "catalogadmin", "prestations", array("id_space"), array(""));
        $this->addRoute("catalogprestationedit", "catalogprestationedit", "catalogadmin", "prestationedit", array("id_space", "id"), array("", ""));
        $this->addRoute("catalogprestationdelete", "catalogprestationdelete", "catalogadmin", "prestationdelete", array("id_space", "id"), array("", ""));
        
    }
}
