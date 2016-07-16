<?php

require_once 'Framework/Request.php';
require_once 'Framework/FormAdd.php';
require_once 'Framework/FormHtml.php';

/**
 * Class allowing to generate and check a form html view. 
 * 
 * @author Sylvain Prigent
 */
class Form {

    /** request */
    private $request;

    /** form info */
    private $title;
    private $subtitle;
    private $id;
    private $parseRequest;
    private $errorMessage;

    /** form description */
    private $types;
    private $names;
    private $values;
    private $labels;
    private $isMandatory;
    private $enabled;
    private $choices;
    private $choicesid;
    private $validated;
    private $useJavascript;

    /** Validations/cancel/delete buttons */
    private $validationButtonName;
    private $validationURL;
    private $cancelButtonName;
    private $cancelURL;
    private $deleteButtonName;
    private $deleteURL;
    private $deleteID;

    /** form display (in bootstrap column number) */
    //private $totalWidth;
    private $labelWidth;
    private $inputWidth;
    private $buttonsWidth;
    private $buttonsOffset;

    /** for download feild */
    private $useUpload;
    private $isDate;
    private $isTextArea;
    private $formAdd;
    private $isFormAdd;

    /**
     * Constructor
     * @param Request $request Request that contains the post data
     * @param unknown $id Form ID
     */
    public function __construct(Request $request, $id) {
        $this->request = $request;
        $this->id = $id;
        $this->labelWidth = 4;
        $this->inputWidth = 6;
        $this->buttonsWidth = 6;
        $this->buttonsOffset = 6;
        $this->title = "";
        $this->validationURL = "";
        $this->cancelURL = "";
        $this->deleteURL = "";
        $this->isTextArea = false;
        $this->isFormAdd = false;

        $this->parseRequest = false;
        $formID = $request->getParameterNoException("formid");
        if ($formID == $this->id) {
            $this->parseRequest = true;
        }

        $this->useUpload = false;
        $this->isDate = false;
    }

    public function setButtonsWidth($buttonsWidth, $buttonsOffset) {
        $this->buttonsWidth = $buttonsWidth;
        $this->buttonsOffset = $buttonsOffset;
    }

    public function setColumnsWidth($labelWidth, $inputWidth) {
        $this->labelWidth = $labelWidth;
        $this->inputWidth = $inputWidth;
    }

    /**
     * Set the form title
     * @param string $title Form title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Set the form sub title
     * @param string $subtitle Form sub title
     */
    public function setSubTitle($subtitle) {
        $this->subtitle = $subtitle;
    }

    /**
     * Set a validation button to the title
     * @param string $name Button text
     * @param string $url URL of the form post query
     */
    public function setValidationButton($name, $url) {
        $this->validationButtonName = $name;
        $this->validationURL = $url;
    }

    /**
     * Set a cancel button to the form
     * @param string $name Button text
     * @param string $url URL redirection
     */
    public function setCancelButton($name, $url) {
        $this->cancelButtonName = $name;
        $this->cancelURL = $url;
    }

    /**
     * set a delete button to the form
     * @param string $name Button text
     * @param string $url URL of the query
     * @param string|number $dataID ID of the data to delete
     */
    public function setDeleteButton($name, $url, $dataID) {
        $this->deleteButtonName = $name;
        $this->deleteURL = $url;
        $this->deleteID = $dataID;
    }

    /**
     * Internal method to set an input value either using the default value, or the request value
     * @param string $name Value name
     * @param string $value Default value
     */
    protected function setValue($name, $value) {
        if ($this->parseRequest) {
            $this->values[] = $this->request->getParameterNoException($name);
        } else {
            $this->values[] = $value;
        }
    }

    public function addSeparator($name) {
        $this->types[] = "separator";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue($name, "");
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }
    
    public function addSeparator2($name) {
        $this->types[] = "separator2";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue($name, "");
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }
    
    

    public function addComment($text) {
        $this->types[] = "comment";
        $this->names[] = $text;
        $this->labels[] = "";
        $this->setValue($text, "");
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }

    /**
     * Add hidden input to the form
     * @param string $name Input name
     * @param string $label Input label 
     * @param string $value Input default value
     */
    public function addHidden($name, $value = "") {
        $this->types[] = "hidden";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue($name, $value);
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }

    public function addUpload($name, $label) {
        $this->types[] = "upload";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->useUpload = true;
        $this->enabled[] = "";
        $this->setValue($name, "");
        $this->useJavascript[] = false;
    }

    public function addDownloadButton($label, $url) {
        $this->types[] = "downloadbutton";
        $this->names[] = $url;
        $this->labels[] = $label;
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }

    /**
     * Add text input to the form
     * @param string $name Input name
     * @param string $label Input label 
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addText($name, $label, $isMandatory = false, $value = "", $enabled = "") {
        $this->types[] = "text";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = $enabled;
        $this->useJavascript[] = false;
    }

    public function addPassword($name, $label, $isMandatory = true) {
        $this->types[] = "password";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, "");
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = true;
        $this->useJavascript[] = false;
    }

    public function addDate($name, $label, $isMandatory = false, $value = "") {
        $this->isDate = true;
        $this->types[] = "date";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }

    /**
     * Add color input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addColor($name, $label, $isMandatory = false, $value = "") {
        $this->types[] = "color";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }

    /**
     * Add email input to the form
     * @param string $name Input name
     * @param string $label Input label 
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addEmail($name, $label, $isMandatory = false, $value = "") {
        $this->types[] = "email";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }

    /**
     * Add number input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addNumber($name, $label, $isMandatory = false, $value = "") {
        $this->types[] = "number";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }

    /**
     * Add select input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param unknown $choices List of options names
     * @param unknown $choicesid List of options ids
     * @param string $value Input default value
     */
    public function addSelect($name, $label, $choices, $choicesid, $value = "") {
        $this->types[] = "select";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $value);
        $this->isMandatory[] = false;
        $this->choices[] = $choices;
        $this->choicesid[] = $choicesid;
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
    }

    /**
     * Add textarea input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addTextArea($name, $label, $isMandatory = false, $value = "", $userichtxt = false) {
        $this->types[] = "textarea";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->isTextArea = $userichtxt;
        $this->useJavascript[] = $userichtxt;
    }
    
    public function addChoicesList($label, $listNames, $listIds, $values){
        $this->types[] = "choicesList";
        $this->names[] = "";
        $this->labels[] = $label;
        $this->isMandatory[] = false;
        $this->choices[] = $listNames;
        $this->choicesid[] = $listIds;
        $this->values[] = $values;
        $this->validated[] = true;
        $this->enabled[] = "";
    }
    
    public function setFormAdd(FormAdd $formAdd, $label = ""){
        $this->formAdd = $formAdd;
        $this->types[] = "formAdd";
        $this->names[] = "";
        $this->labels[] = $label;
        $this->values[] = "";
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->isFormAdd = true;
    }

    /**
     * Generate the html code
     * @return string
     */
    public function getHtml($lang = "en") {

        $html = "";

        $formHtml = new FormHtml();
        
        $html .= $formHtml->title($this->title, $this->subtitle);
        $html .= $formHtml->errorMessage($this->errorMessage);
        $html .= $formHtml->formHeader($this->validationURL, $this->useUpload);
        $html .= $formHtml->id($this->id);
        
        // fields
        for ($i = 0; $i < count($this->types); $i++) {

            $required = "";
            if ($this->isMandatory[$i]) {
                $required = "required";
            }
            $validated = "";
            if ($this->validated[$i] == false) {
                $validated = "alert alert-danger";
            }

            if ($this->types[$i] == "separator"){
                $html .= $formHtml->separator($this->names[$i], 3);
            }
            if ($this->types[$i] == "separator2"){
                $html .= $formHtml->separator($this->names[$i], 5);
            }
            if ($this->types[$i] == "comment") {
                $html .= $formHtml->comment($this->names[$i]);
            }
            if ($this->types[$i] == "hidden") {
                $html .= $formHtml->hidden($this->names[$i], $this->values[$i], $required);
            }
            if ($this->types[$i] == "text") {
                $html .= $formHtml->text($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $this->enabled[$i], $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "password") {
                $html .= $formHtml->password($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $this->enabled[$i], $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "date") {
                $html .= $formHtml->date($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $lang, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "color") {
                $html .= $formHtml->color($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "email") {
                $html .= $formHtml->email($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "number") {
                $html .= $formHtml->number($this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "textarea") {
                $html .= $formHtml->textarea($this->useJavascript[$i], $this->labels[$i], $this->names[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "upload") {
                $html .= $formHtml->upload($this->labels[$i], $this->names[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "downloadbutton") {
                $html .= $formHtml->downloadbutton($this->labels[$i], $this->names[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "select") {
                $html .= $formHtml->select($this->labels[$i], $this->names[$i], $this->choices[$i], $this->choicesid[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "formAdd"){
                $html .= $this->formAdd->getHtml($lang, $this->labels[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "choicesList"){
                $html .= $formHtml->choicesList($this->labels[$i], $this->choices[$i], $this->choicesid[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
            }
        }

        // buttons area
        $html .= $formHtml->buttons($this->validationURL, $this->validationButtonName, $this->cancelURL, $this->cancelButtonName, $this->deleteURL, $this->deleteID, $this->deleteButtonName, $this->buttonsWidth, $this->buttonsOffset);
        $html .= $formHtml->formFooter();

        if ($this->isDate == true) {
            $html .= $formHtml->timePickerScript();
        }
        if ($this->isTextArea == true) {
            $html .= $formHtml->textAreaScript();
        }
        if ($this->isFormAdd == true){
            $html .= $this->formAdd->getJavascript();
        }

        return $html;
    }

    /**
     * Check if the form is valid
     * @return number
     */
    public function check() {

        $formID = $this->request->getParameterNoException("formid");
        //echo "this id = " . $this->id . ", formID = " . $formID . "<br/>";
        if ($formID == $this->id) {
            for ($i = 0; $i < count($this->types); $i++) {
                if ($this->types[$i] == "email") {
                    //echo "check email " . $this->request->getParameter($this->names[$i]) . " <br/>";

                    if (!preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $this->request->getParameter($this->names[$i]))) {
                        $this->validated[$i] = false;
                        $this->errorMessage = "The email address is not valid";
                        return 0;
                    }
                }
            }

            return 1;
        }

        return 0;
    }

    /**
     * Get input from query
     * @param unknown $name Input name
     * @return string Input value
     */
    public function getParameter($name) {
        return $this->request->getParameter($name);
    }

}
