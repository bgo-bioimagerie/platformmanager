<?php

require_once 'Framework/Model.php';

class EsProductCategory extends Model {

    protected $productCategoryClass;
    
    public function __construct($id_space) {
        $modelConfig = new CoreConfig();
        $productClassName = $modelConfig->getParamSpace("estoreProductCategoryClass", $id_space);
        
        if ($productClassName == ""){
            require_once "Modules/estore/Model/EsProductCategoryDefault.php";
            $this->productCategoryClass = new EsProductCategoryDefault();
        }
        else{
            $productClassNameArray = explode("::", $productClassName);
            if (count($productClassNameArray) != 3){
                throw new Exception("estoreProductCategoryClass not valid");
            }
            $fileName = "Modules/".$productClassNameArray[0]."/".$productClassNameArray[1]."/".$productClassNameArray[2].".php";
            require_once $fileName;
            $this->productCategoryClass = new $productClassNameArray[2]();
        }
    }
    
    public function getFirstId($id_space){
        return $this->productCategoryClass->getFirstId($id_space);
    }

    public function getAll($id_space) {
        return $this->productCategoryClass->getAll($id_space);
    }
    
    public function get($id_space, $id) {
       return $this->productCategoryClass->get($id_space, $id);
    }
    
    public function getName($id_space ,$id) {
        return $this->productCategoryClass->getName($id_space ,$id);
    }

    public function getVat($id_space ,$id){
        return $this->productCategoryClass->getVat($id_space, $id);
    }
    
    public function set($id, $id_space, $name, $description) {
        return $this->productCategoryClass->set($id, $id_space, $name, $description);
    }
    
    public function getForList($id_space){
        return $this->productCategoryClass->getForList($id_space);
    }
    
    public function delete($id_space ,$id) {
        return $this->productCategoryClass->delete($id_space, $id);
    }

}
