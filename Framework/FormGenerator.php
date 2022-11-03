<?php

require_once 'Framework/Form.php';

/**
 * Class allowing to generate and check a form html view.
 *
 * @author Sylvain Prigent
 */
class FormGenerator
{
    protected $form;
    protected $lang;
    protected $request;
    protected $id;
    protected $validationUrl;
    protected $data;
    protected $title;


    public function __construct(Request $request, $id, $url)
    {
        $this->request = $request;
        $this->id = $id;
        $this->validationUrl = $url;
        $this->lang = 'en';
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData($index)
    {
        if (isset($this->data[$index])) {
            return $this->data[$index];
        }
        return "";
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getHtml()
    {
        return $this->form->getHtml($this->lang);
    }
}
