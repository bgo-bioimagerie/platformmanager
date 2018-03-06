<?php

require_once 'Framework/Model.php';

class EsProduct extends Model {

    protected $productClass;
    
    public function __construct($id_space) {
        
        $modelConfig = new CoreConfig();
        $productClassName = $modelConfig->getParamSpace("estoreProductClass", $id_space);
        
        if ($productClassName == ""){
            require_once "Modules/estore/Model/EsProductDefault.php";
            $this->productClass = new EsProductDefault();
        }
        else{
            $productClassNameArray = explode("::", $productClassName);
            if (count($productClassNameArray) != 3){
                throw new Exception("estoreProductClass not valid");
            }
            $fileName = "Modules/".$productClassNameArray[0]."/".$productClassNameArray[1]."/".$productClassNameArray[2].".php";
            require_once $fileName;
            $this->productClass = new $productClassNameArray[2]();
        }
    }

    public function getAll($id_space) {
        return $this->productClass->getAll($id_space);
    }
    
    public function getByCategory($id_category){
        return $this->productClass->getByCategory($id_category);
    }

    public function getForCategory($id_category) {
        return $this->productClass->getForCategory($id_category);
    }

    public function get($id) {
        return $this->productClass->get($id);
    }
    
    public function getName($id){
        return $this->productClass->getName($id);
    }

    public function set($id, $id_space, $id_category, $name, $description, $vat) {
        return $this->productClass->set($id, $id_space, $id_category, $name, $description, $vat);
    }

    public function setQuantity($id, $quantity) {
        return $this->productClass->setQuantity($id, $quantity);
    }

    public function setImage($id, $url) {
        return $this->productClass->setImage($id, $url);
    }

    public function delete($id) {
        return $this->productClass->delete($id);
    }

}
