<?php

require_once 'Framework/Request.php';

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
    private $useDownload;
    private $isDate;
    private $isTextArea;

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

        $this->parseRequest = false;
        $formID = $request->getParameterNoException("formid");
        if ($formID == $this->id) {
            $this->parseRequest = true;
        }

        $this->useDownload = false;
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

    public function addDownload($name, $label) {
        $this->types[] = "download";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->useDownload = true;
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
        $this->isTextArea = true;
        $this->useJavascript[] = $userichtxt;
    }

    /**
     * Generate the html code
     * @return string
     */
    public function getHtml($lang = "Fr") {

        $html = "";

        // form title
        if ($this->title != "") {
            $html .= "<div class=\"page-header\">";
            $html .= "<h1>" . $this->title;
            if ($this->subtitle != "") {
                $html .= "<br/><small>" . $this->subtitle . "</small>";
            }
            $html .= "</h1>";
            $html .= "</div>";
        }

        if ($this->errorMessage != "") {
            $html .= "<div class=\"alert alert-danger text-center\">";
            $html .= "<p>" . $this->errorMessage . "</p>";
            $html .= "</div>";
        }

        // form header
        if (!$this->useDownload) {
            $html .= "<form role=\"form\" class=\"form-horizontal\" action=\"" . $this->validationURL . "\" method=\"post\">";
        } else {
            $html .= "<form role=\"form\" class=\"form-horizontal\" action=\"" . $this->validationURL . "\" method=\"post\" enctype=\"multipart/form-data\">";
        }

        // form id
        $html .= "<input class=\"form-control\" type=\"hidden\" name=\"formid\" value=\"" . $this->id . "\" />";


        for ($i = 0; $i < count($this->types); $i++) {

            $required = "";
            if ($this->isMandatory[$i]) {
                $required = "required";
            }
            $validated = "";
            if ($this->validated[$i] == false) {
                $validated = "alert alert-danger";
            }

            if ($this->types[$i] == "separator") {
                $html .= "<div class=\"page-header\">";
                $html .= "<h3>" . $this->names[$i] . "</h3>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "separator2") {
                $html .= "<div class=\"page-header\">";
                $html .= "<h5>" . $this->names[$i] . "</h5>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "comment") {
                $html .= "<div >";
                $html .= "<p>" . $this->names[$i] . "</p>";
                $html .= "</div>";
            }

            if ($this->types[$i] == "hidden") {
                $html .= "<input class=\"form-control\" type=\"hidden\" name=\"" . $this->names[$i] . "\"";
                $html .= " value=\"" . $this->values[$i] . "\"" . $required;
                $html .= "/>";
            }
            if ($this->types[$i] == "text") {
                $html .= "<div class=\"form-group" . $validated . "\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= "<input class=\"form-control\" type=\"text\" name=\"" . $this->names[$i] . "\"";
                $html .= " value=\"" . $this->values[$i] . "\"" . $required . " " . $this->enabled[$i];
                $html .= "/>";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "password") {
                $html .= "<div class=\"form-group" . $validated . "\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= "<input class=\"form-control\" type=\"password\" name=\"" . $this->names[$i] . "\"";
                $html .= " value=\"" . $this->values[$i] . "\"" . $required . " " . $this->enabled[$i];
                $html .= "/>";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "date") {

                $html .= "<div class=\"form-group" . $validated . "\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";

                $html .= "<div class='col-xs-" . $this->labelWidth . "'>";
                $html .= "<div class='col-xs-12 input-group date form_date_" . $lang . "'>";
                $html .= "<input id=\"date-daily\" type='text' class=\"form-control\" name=\"" . $this->names[$i] . "\" value=\"" . $this->values[$i] . "\"/>";
                $html .= "          <span class=\"input-group-addon\">";
                $html .= "          <span class=\"glyphicon glyphicon-calendar\"></span>";
                $html .= "          </span>";
                $html .= "</div>";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "color") {
                $html .= "<div class=\"form-group" . $validated . "\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= "<input class=\"form-control\" type=\"color\" name=\"" . $this->names[$i] . "\"";
                $html .= " value=\"" . $this->values[$i] . "\"" . $required;
                $html .= "/>";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "email") {
                $html .= "<div class=\"form-group " . $validated . "\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= "<input class=\"form-control\" type=\"text\" name=\"" . $this->names[$i] . "\"";
                $html .= " value=\"" . $this->values[$i] . "\"" . $required;
                $html .= "/>";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "number") {
                $html .= "<div class=\"form-group\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= "<input class=\"form-control\" type=\"number\" name=\"" . $this->names[$i] . "\"";
                $html .= " value=\"" . $this->values[$i] . "\"" . $required;
                $html .= "/>";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "textarea") {
                $divid = "";
                if ($this->useJavascript[$i]) {
                    $divid = "id='editor'";
                }
                $html .= "<div class=\"form-group\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= "<textarea " . $divid . " class=\"form-control\" name=\"" . $this->names[$i] . "\">" . $this->values[$i] . "</textarea>";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "download") {
                $html .= "<div class=\"form-group\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= " <input type=\"file\" name=\"" . $this->names[$i] . "\" id=\"" . $this->names[$i] . "\"> ";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "downloadbutton") {
                $html .= "<div class=\"form-group\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= "<button class=\"btn btn-default\" type=\"button\" onclick=\"location.href = '" . $this->names[$i] . "'\">" . $this->labels[$i] . "</button>";
                $html .= "</div>";
                $html .= "</div>";
            }
            if ($this->types[$i] == "select") {
                $html .= "<div class=\"form-group\">";
                $html .= "<label class=\"control-label col-xs-" . $this->labelWidth . "\">" . $this->labels[$i] . "</label>";
                $html .= "	<div class=\"col-xs-" . $this->inputWidth . "\">";
                $html .= "	<select class=\"form-control\" name=\"" . $this->names[$i] . "\">";
                for ($v = 0; $v < count($this->choices[$i]); $v++) {
                    $selected = "";
                    if ($this->values[$i] == $this->choicesid[$i][$v]) {
                        $selected = "selected=\"selected\"";
                    }
                    $html .= "<OPTION value=\"" . $this->choicesid[$i][$v] . "\"" . $selected . ">" . $this->choices[$i][$v] . "</OPTION>";
                }
                $html .= "</select>";
                $html .= "</div>";
                $html .= "</div>";
            }
        }

        // buttons area
        $html .= "<div class=\"col-xs-" . $this->buttonsWidth . " col-xs-offset-" . $this->buttonsOffset . "\">";
        if ($this->validationURL != "") {
            $html .= "<input type=\"submit\" class=\"btn btn-primary\" value=\"" . $this->validationButtonName . "\" />";
        }
        if ($this->cancelURL != "") {
            $html .= "<button type=\"button\" onclick=\"location.href='" . $this->cancelURL . "'\" class=\"btn btn-default\">" . $this->cancelButtonName . "</button>";
        }
        if ($this->deleteURL != "") {
            $html .= "<button type=\"button\" onclick=\"location.href='" . $this->deleteURL . "/" . $this->deleteID . "'\" class=\"btn btn-danger\">" . $this->deleteButtonName . "</button>";
        }
        $html .= "</div>";
        $html .= "</form>";

        if ($this->isDate == true) {
            $html .= file_get_contents("Framework/timepicker_script.php");
        }
        if ($this->isTextArea == true) {
            $html .= file_get_contents("Framework/textarea_script.php");
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
