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
    
    public function getByCategory($id_space, $id_category){
        return $this->productClass->getByCategory($id_space ,$id_category);
    }

    public function getForCategory($id_space, $id_category) {
        return $this->productClass->getForCategory($id_space, $id_category);
    }

    public function get($id_space, $id) {
        return $this->productClass->get($id_space ,$id);
    }
    
    public function getName($id_space, $id){
        return $this->productClass->getName($id_space, $id);
    }

    public function set($id, $id_space, $id_category, $name, $description, $vat) {
        return $this->productClass->set($id, $id_space, $id_category, $name, $description, $vat);
    }

    public function setQuantity($id_space ,$id, $quantity) {
        return $this->productClass->setQuantity($id_space, $id, $quantity);
    }

    public function setImage($id_space, $id, $url) {
        return $this->productClass->setImage($id_space, $id, $url);
    }

    public function delete($id_space, $id) {
        return $this->productClass->delete($id_space, $id);
    }

}
