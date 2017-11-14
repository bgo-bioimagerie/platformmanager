<?php

require_once 'Framework/FormGenerator.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';

require_once 'Modules/breeding/Model/BrBatch.php';

class BatchInfoForm extends FormGenerator{

    private $id_space;
    
    public function setSpace($id_space){
        $this->id_space = $id_space;
    }
    
    /**
     * Constructor
     */
    public function __construct(Request $request,  $id, $url) {
        parent::__construct($request,  $id, $url);
        
    }
    
    public function render(){
        
        // select data 
        $modelBudget = new BrBatch();
        $batchs = $modelBudget->getForList($this->id_space);
        
        $destinations["names"] = array( BreedingTranslator::Sale($this->lang), BreedingTranslator::Lab($this->lang));
        $destinations["ids"] = array(1,2);

        $productsModel = new BrProduct();
        $products = $productsModel->getForList($this->id_space);

        // Form
        $this->form = new Form($this->request, $this->id);
        $this->form->setTitle($this->title, 3);
        
        $this->form->addHidden("id", $this->getData("id"));
        $this->form->addText("reference", BreedingTranslator::Reference($this->lang), true, $this->getData("reference"));
        $this->form->addDate("created", BreedingTranslator::Created($this->lang), true, CoreTranslator::dateFromEn($this->getData("created"), $this->lang));
        $this->form->addSelect("id_male_spawner", BreedingTranslator::MaleSpawner($this->lang), $batchs["names"], $batchs["ids"], $this->getData("id_male_spawner"));
        $this->form->addSelect("id_female_spawner", BreedingTranslator::FemaleSpawner($this->lang), $batchs["names"], $batchs["ids"], $this->getData("id_female_spawner"));
        $this->form->addSelectMandatory("id_destination", BreedingTranslator::Destination($this->lang), $destinations["names"], $destinations["ids"], $this->getData("id_destination"));
        $this->form->addSelectMandatory("id_product", BreedingTranslator::Product($this->lang), $products["names"], $products["ids"], $this->getData("id_product"));
        $this->form->addNumber("quantity_start", BreedingTranslator::InitialQuantity($this->lang), true, $this->getData("quantity_start"));
        
        $this->form->addSelectMandatory("chipped", BreedingTranslator::Chipped($this->lang), array(coreTranslator::no($this->lang), coreTranslator::yes($this->lang)), array(0,1), $this->getData("chipped"));
        $this->form->addTextArea("comment", BreedingTranslator::Comment($this->lang), false, $this->getData("comment"));
        
        $this->form->setValidationButton(CoreTranslator::Save($this->lang), $this->validationUrl); 
        $this->form->setButtonsWidth(2, 10);
    }
   
    public function save(){
        $model = new BrBatch();
        $id = $model->set(
            $this->request->getParameter("id"), 
            $this->id_space, 
            $this->request->getParameter("reference"), 
            CoreTranslator::DateToEn( $this->request->getParameter("created"), $this->lang), 
            $this->request->getParameterNoException("id_male_spawner"), 
            $this->request->getParameterNoException("id_female_spawner"), 
            $this->request->getParameter("id_destination"), 
            $this->request->getParameter("id_product"),  
            $this->request->getParameter("chipped"), 
            $this->request->getParameter("comment") 
        );
            
        $model->setQuantityStart($id, $this->request->getParameter("quantity_start"));
        $model->updateQuantity($id);
        return $id;
    }
    
}

