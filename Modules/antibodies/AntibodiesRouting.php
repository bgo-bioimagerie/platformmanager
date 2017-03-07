<?php

require_once 'Framework/Routing.php';

class AntibodiesRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("antibodiesconfigadmin", "antibodiesconfigadmin", "antibodiesconfigadmin", "index");
        $this->addRoute("antibodiesconfig", "antibodiesconfig", "antibodiesconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("antibodies", "antibodies", "antibodieslist", "index", array("id_space"), array(""));
        $this->addRoute("apiantibodytissus", "apiantibodytissus", "antibodies", "tissus", array("id_space", "id_tissus"), array("", ""),true);
        $this->addRoute("deletetissus", 'deletetissus', 'antibodieslist', "deletetissus", array('id_space', 'id_tissus'), array("", ""));
        $this->addRoute("deleteowner", 'deleteowner', 'antibodieslist', "deleteowner", array('id_space', 'id_owner'), array("", ""));
        
        
        $this->addRoute("apiantibodyowner", "apiantibodyowner", "antibodies", "owner", array("id_space", "id_owner"), array("", ""),true);
        $this->addRoute("antibodiesedittissus", "antibodiesedittissus", "antibodieslist", "edittissus", array("id_space"), array(""));
        $this->addRoute("antibodieseditowner", "antibodieseditowner", "antibodieslist", "editowner", array("id_space"), array(""));
        
        
        // acii
        $this->addRoute("acii", "acii", "acii", "index", array("id_space"), array(""));
        $this->addRoute("aciiedit", "aciiedit", "acii", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("aciidelete", "aciidelete", "acii", "delete", array("id_space", "id"), array("", ""));
        // aciinc
        $this->addRoute("aciinc", "aciinc", "aciinc", "index", array("id_space"), array(""));
        $this->addRoute("aciincedit", "aciincedit", "aciinc", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("aciincdelete", "aciincdelete", "aciinc", "delete", array("id_space", "id"), array("", ""));
        // application
        $this->addRoute("application", "application", "application", "index", array("id_space"), array(""));
        $this->addRoute("applicationedit", "applicationedit", "application", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("applicationdelete", "applicationdelete", "application", "delete", array("id_space", "id"), array("", ""));
        // dem
        $this->addRoute("dem", "dem", "dem", "index", array("id_space"), array(""));
        $this->addRoute("demedit", "demedit", "dem", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("demdelete", "demdelete", "dem", "delete", array("id_space", "id"), array("", ""));
        // enzymes
        $this->addRoute("enzymes", "enzymes", "enzymes", "index", array("id_space"), array(""));
        $this->addRoute("enzymesedit", "enzymesedit", "enzymes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("enzymesdelete", "enzymesdelete", "enzymes", "delete", array("id_space", "id"), array("", ""));
        // especes
        $this->addRoute("especes", "especes", "especes", "index", array("id_space"), array(""));
        $this->addRoute("especesedit", "especesedit", "especes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("especesdelete", "especesdelete", "especes", "delete", array("id_space", "id"), array("", ""));
        // fixative
        $this->addRoute("fixative", "fixative", "fixative", "index", array("id_space"), array(""));
        $this->addRoute("fixativeedit", "fixativeedit", "fixative", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("fixativedelete", "fixativedelete", "fixative", "delete", array("id_space", "id"), array("", ""));
        // inc
        $this->addRoute("inc", "inc", "inc", "index", array("id_space"), array(""));
        $this->addRoute("incedit", "incedit", "inc", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("incdelete", "incdelete", "inc", "delete", array("id_space", "id"), array("", ""));
        // isotypes
        $this->addRoute("isotypes", "isotypes", "isotypes", "index", array("id_space"), array(""));
        $this->addRoute("isotypesedit", "isotypesedit", "isotypes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("isotypesdelete", "isotypesdelete", "isotypes", "delete", array("id_space", "id"), array("", ""));
        // kit
        $this->addRoute("kit", "kit", "kit", "index", array("id_space"), array(""));
        $this->addRoute("kitedit", "kitedit", "kit", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("kitdelete", "kitdelete", "kit", "delete", array("id_space", "id"), array("", ""));
        // linker
        $this->addRoute("linker", "linker", "linker", "index", array("id_space"), array(""));
        $this->addRoute("linkeredit", "linkeredit", "linker", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("linkerdelete", "linkerdelete", "linker", "delete", array("id_space", "id"), array("", ""));
        // option
        $this->addRoute("option", "option", "option", "index", array("id_space"), array(""));
        $this->addRoute("optionedit", "optionedit", "option", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("optiondelete", "optiondelete", "option", "delete", array("id_space", "id"), array("", ""));
        // organes
        $this->addRoute("organes", "organes", "organes", "index", array("id_space"), array(""));
        $this->addRoute("organesedit", "organesedit", "organes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("organesdelete", "organesdelete", "organes", "delete", array("id_space", "id"), array("", ""));
        // prelevements
        $this->addRoute("prelevements", "prelevements", "prelevements", "index", array("id_space"), array(""));
        $this->addRoute("prelevementsedit", "prelevementsedit", "prelevements", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("prelevementsdelete", "prelevementsdelete", "prelevements", "delete", array("id_space", "id"), array("", ""));
        // proto
        $this->addRoute("proto", "proto", "proto", "index", array("id_space"), array(""));
        $this->addRoute("protoedit", "protoedit", "proto", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("protodelete", "protodelete", "proto", "delete", array("id_space", "id"), array("", ""));
        // sources
        $this->addRoute("sources", "sources", "sources", "index", array("id_space"), array(""));
        $this->addRoute("sourcesedit", "sourcesedit", "sources", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("sourcesdelete", "sourcesdelete", "sources", "delete", array("id_space", "id"), array("", ""));
        // staining
        $this->addRoute("staining", "staining", "staining", "index", array("id_space"), array(""));
        $this->addRoute("stainingedit", "stainingedit", "staining", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("stainingdelete", "stainingdelete", "staining", "delete", array("id_space", "id"), array("", ""));
        // status
        $this->addRoute("status", "status", "status", "index", array("id_space"), array(""));
        $this->addRoute("statusedit", "statusedit", "status", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("statusdelete", "statusdelete", "status", "delete", array("id_space", "id"), array("", ""));
        // anticorps
        $this->addRoute("anticorps", "anticorps", "antibodieslist", "index", array("id_space", "sortentry"), array("", ""));
        $this->addRoute("anticorpsedit", "anticorpsedit", "antibodieslist", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("anticorpseditquery", "anticorpseditquery", "antibodieslist", "editquery", array("id_space", "id"), array("", ""));
        // search
        $this->addRoute("anticorpsadvsearchquery", "anticorpsadvsearchquery", "antibodieslist", "advsearchquery", array("id_space", "source"), array("", ""));
        
        // protocols
        $this->addRoute("protocols", "protocols", "protocols", "index", array("id_space", "sortEntry"), array("", ""));
        $this->addRoute("protocolsedit", "protocolsedit", "protocols", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("protocolseditquery", "protocolseditquery", "protocols", "editquery", array("id_space"), array(""));
        $this->addRoute("protocolsdelete", "protocolsdelete", "protocols", "delete", array("id_space", "id"), array("", ""));
        
    }
}
