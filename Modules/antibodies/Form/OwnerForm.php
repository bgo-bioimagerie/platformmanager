<?php

require_once 'Framework/FormGenerator.php';
require_once 'Modules/antibodies/Model/AntibodiesTranslator.php';

class OwnerForm extends FormGenerator{

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
        $this->form = new Form($this->request, $this->id);
        $this->form->setTitle(AntibodiesTranslator::Owner($this->lang));
        
        $this->form->addHidden("owner_id");
        $this->form->addHidden("owner_id_anticorps");
        
        $modelUser = new EcUser();
        $userList = $modelUser->getAcivesForSelect("name");
        $this->form->addSelect('owner_id_user', AntibodiesTranslator::Owner($this->lang), 
                $userList["names"], $userList["ids"]);
        
        $this->form->addSelect('owner_disponible', AntibodiesTranslator::Disponible($this->lang), 
                array('disponible', 'épuisé', 'récupéré par équipe'), array(1,2,3));
        
        $this->form->addDate('owner_date_recept', AntibodiesTranslator::Date_recept($this->lang));
        $this->form->addText("owner_no_dossier", AntibodiesTranslator::No_dossier($this->lang));
        
        $this->form->setValidationButton(CoreTranslator::Save($this->lang), $this->validationUrl); 
        $this->form->setButtonsWidth(2, 10);
    }
   
    
}

