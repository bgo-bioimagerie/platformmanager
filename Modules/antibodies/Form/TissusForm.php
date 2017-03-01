<?php

require_once 'Framework/FormGenerator.php';
require_once 'Modules/antibodies/Model/AntibodiesTranslator.php';

class TissusForm extends FormGenerator{

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
        $this->form->setTitle(AntibodiesTranslator::Tissus($this->lang));
        
        $this->form->addHidden("id");
        $this->form->addHidden("id_antibody");
        
        $modelProto = new AcProtocol();
        $protoList = $modelProto->getForList($this->id_space);
        $this->form->addSelect('ref_protocol', AntibodiesTranslator::Ref_protocol($this->lang), 
                $protoList["names"], $protoList["ids"]);
        
        $this->form->addText("dilution", AntibodiesTranslator::Dilution($this->lang));
        $this->form->addTextArea("comment", AntibodiesTranslator::Comment($this->lang));
        
        $modelEspece = new Espece();
        $especeList = $modelEspece->getForList($this->id_space);
        $this->form->addSelect('espece', AntibodiesTranslator::Espece($this->lang), 
                $especeList["names"], $especeList["ids"]);
        
        $modelOrgane = new Organe();
        $organeList = $modelOrgane->getForList($this->id_space);
        $this->form->addSelect('organe', AntibodiesTranslator::Organe($this->lang), 
                $organeList["names"], $organeList["ids"]);
        
        $modelStatus = new Status();
        $statusList = $modelStatus->getForList($this->id_space);
        $this->form->addSelect('status', AntibodiesTranslator::Valide($this->lang), 
                $statusList["names"], $statusList["ids"]);
        
        $this->form->addText("ref_bloc", AntibodiesTranslator::Ref_bloc($this->lang));
        
        $modelPrelevement = new Prelevement();
        $prelevementList = $modelPrelevement->getForList($this->id_space);
        $this->form->addSelect('prelevement', AntibodiesTranslator::Prelevement($this->lang), 
                $prelevementList["names"], $prelevementList["ids"]);
        
        
        $this->form->addUpload("image_url", AntibodiesTranslator::Image($this->lang));
        
        $this->form->setValidationButton(CoreTranslator::Save($this->lang), $this->validationUrl); 
        $this->form->setButtonsWidth(2, 10);
    }
   
    
}

