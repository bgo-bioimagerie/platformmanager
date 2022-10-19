<?php

require_once 'Framework/FormGenerator.php';
require_once 'Modules/antibodies/Model/AntibodiesTranslator.php';

class TissusForm extends FormGenerator
{
    private $id_space;

    public function setSpace($id_space)
    {
        $this->id_space = $id_space;
    }

    public function render()
    {
        $this->form = new Form($this->request, $this->id);
        $this->form->setTitle(AntibodiesTranslator::Tissus($this->lang));

        $this->form->addHidden("id");
        $this->form->addHidden("id_antibody");

        $modelProto = new AcProtocol();
        $protoList = $modelProto->getForList($this->id_space);
        $this->form->addSelectMandatory(
            'ref_protocol',
            AntibodiesTranslator::Ref_protocol($this->lang),
            $protoList["names"],
            $protoList["ids"]
        );

        $this->form->addText("dilution", AntibodiesTranslator::Dilution($this->lang));
        $this->form->addTextArea("comment", AntibodiesTranslator::Comment($this->lang));

        $modelEspece = new Espece();
        $especeList = $modelEspece->getForList($this->id_space);
        $this->form->addSelectMandatory(
            'espece',
            AntibodiesTranslator::Espece($this->lang),
            $especeList["names"],
            $especeList["ids"]
        );

        $modelOrgane = new Organe();
        $organeList = $modelOrgane->getForList($this->id_space);
        $this->form->addSelectMandatory(
            'organe',
            AntibodiesTranslator::Organe($this->lang),
            $organeList["names"],
            $organeList["ids"]
        );

        $modelStatus = new Status();
        $statusList = $modelStatus->getForList($this->id_space);
        $this->form->addSelectMandatory(
            'status',
            AntibodiesTranslator::Valide($this->lang),
            $statusList["names"],
            $statusList["ids"]
        );

        $this->form->addText("ref_bloc", AntibodiesTranslator::Ref_bloc($this->lang));

        $modelPrelevement = new Prelevement();
        $prelevList = $modelPrelevement->getForList($this->id_space);
        $this->form->addSelectMandatory(
            'prelevement',
            AntibodiesTranslator::Prelevement($this->lang),
            $prelevList["names"],
            $prelevList["ids"]
        );

        $this->form->addUpload("image_url", AntibodiesTranslator::Image($this->lang));

        $this->form->setValidationButton(CoreTranslator::Save($this->lang), $this->validationUrl);
    }
}
