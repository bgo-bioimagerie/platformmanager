<?php

require_once 'Framework/FormGenerator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

require_once 'Modules/clients/Model/ClAddress.php';

class AddressForm extends FormGenerator
{
    private $id_space;
    protected $cancelUrl;

    public function setSpace($id_space)
    {
        $this->id_space = $id_space;
    }

    /**
     * Constructor
     */
    public function __construct(Request $request, $id, $url, $cancelUrl=null)
    {
        parent::__construct($request, $id, $url);
        $this->cancelUrl = $cancelUrl;
    }

    public function render()
    {
        $this->form = new Form($this->request, $this->id);
        $this->form->setTitle($this->title, 3);
        $this->form->addHidden("id", $this->getData("id"));

        $this->form->addText("institution", ClientsTranslator::Institution($this->lang), true, $this->getData("institution"));
        $this->form->addText("building_floor", ClientsTranslator::BuildingFloor($this->lang), false, $this->getData("building_floor"));
        $this->form->addText("service", ClientsTranslator::Service($this->lang), true, $this->getData("service"));
        $this->form->addText("address", ClientsTranslator::Address($this->lang), true, $this->getData("address"));
        $this->form->addText("zip_code", ClientsTranslator::Zip_code($this->lang), true, $this->getData("zip_code"));
        $this->form->addText("city", ClientsTranslator::City($this->lang), true, $this->getData("city"));
        $this->form->addText("country", ClientsTranslator::Country($this->lang), true, $this->getData("country"));
        $this->form->setValidationButton(CoreTranslator::Save($this->lang), $this->validationUrl);
        if ($this->cancelUrl) {
            $this->form->setCancelButton(CoreTranslator::Cancel($this->lang), $this->cancelUrl);
        }
    }

    public function save()
    {
        $model = new ClAddress();
        return $model->set(
            $this->id_space,
            $this->request->getParameter("id"),
            $this->request->getParameter("institution"),
            $this->request->getParameterNoException("building_floor") ?? '',
            $this->request->getParameter("service"),
            $this->request->getParameter("address"),
            $this->request->getParameter("zip_code"),
            $this->request->getParameter("city"),
            $this->request->getParameter("country")
        );
    }
}
