<?php

require_once 'Framework/Model.php';

require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeAssay extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        /*
        $this->tableName = "se_assay";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_resa", "int(11)", 0);
        $this->setColumnsInfo("id_assay", "int(11)", 0);
        $this->setColumnsInfo("dataurl", "varchar(255)", "");
        $this->primaryKey = "id";
         */
    }
    
    public function getAssays(){
        $modelConfig = new CoreConfig();
        $seekURL = $modelConfig->getParam("seekurl");
        
        $assaysUrl = $seekURL . '/assays.xml';
        
        $assays = array();

        $document_xml = new \DOMDocument();
        $document_xml->loadXML(file_get_contents($assaysUrl));
        $elements = $document_xml->getElementsByTagName('items');
        foreach ($elements as $element) {
            $enfants = $element->childNodes;
            foreach ($enfants as $enfant) {

                if ($enfant->nodeName == "assay") {

                    $childs = $enfant->childNodes;
                    //print_r($childs);
                    $id = 0;
                    $name = "";

                    foreach ($childs as $child) {
                        if ($child->nodeName == 'id') {
                            $id = $nom = $child->nodeValue;
                        } else if ($child->nodeName == 'dc:title') {
                            $name = $nom = $child->nodeValue;
                        }
                    }
                    if ($id != 0 && $name != "") {
                        $assays[] = array('id' => $id, 'name' => $name);
                    }
                }
            }
        }

        return $assays;
    }

    public function getAssaysForList(){
        $assays = $this->getAssays();
        $names = array();
        $ids = array();
        $names[] = "";
        $ids[] = 0;
        foreach($assays as $assay){
            $names[] = $assay["name"];
            $ids[] = $assay["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

}
