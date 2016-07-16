<?php

require_once 'Framework/Form.php';

class FormAdd {

    protected $request;
    protected $id;
    protected $types;
    protected $names;
    protected $labels;
    protected $values;
    protected $isMandatory;
    protected $choices;
    protected $choicesid;
    protected $parseRequest;
    protected $addButtonName;
    protected $removeButtonName;

    public function __construct(Request $request, $id) {
        $this->request = $request;
        $this->id = $id;
        $this->parseRequest = false;
    }

    public function setButtonsNames($addButtonName, $removeButtonName) {
        $this->addButtonName = $addButtonName;
        $this->removeButtonName = $removeButtonName;
    }

    protected function setValue($name, $value) {
        if ($this->parseRequest) {
            $this->values[] = $this->request->getParameterNoException($name);
        } else {
            $this->values[] = $value;
        }
    }

    public function addHidden($name, $values){
        $this->types[] = "hidden";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }
    
    public function addSelect($name, $label, $choices, $choicesid, $values = array()) {
        $this->types[] = "select";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = $choices;
        $this->choicesid[] = $choicesid;
    }

    public function addText($name, $label, $values = array()) {
        $this->types[] = "text";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }
    
    public function addNumber($name, $label, $values = array()) {
        $this->types[] = "number";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

    public function getHtml($lang = "en", $label = "", $labelWidth = 2, $inputWidth = 9) {

        $html = "";
        if ($label != ""){
            $html = "<div class=\"form-group\">";
            $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
            $html .= "	<div class=\"col-xs-" . $inputWidth . "\">";
        }
        else{
            $html .= "<div class=\"form-group\">";
            $html .= "	<div class=\"col-xs-12\">";
        }
        
        $html .= "<table id=\"dataTable\" class=\"table table-striped\"> ";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th></th>";
        for($l = 0 ; $l < count($this->labels) ; $l++){
            if ($this->types[$l] == "hidden"){
                $html .= "<th style=\"width:0em;\"> </th>";
            }
            else{
                $html .= "<th style=\"min-width:10em;\">" . $this->labels[$l] . "</th>";
            }
        }
        
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";

        $formHtml = new FormHtml();
        if (count($this->values[0]) > 0) {
            for ($i = 0; $i < count($this->values[0]); $i++) {
                $html .= "<tr>";
                $html .= "<td><input type=\"checkbox\" name=\"chk\"/></td>";
                for ($j = 0; $j < count($this->names); $j++) {
                    $html .= "<td>";
                    if ($this->types[$j] == "select") {
                        $html .= $formHtml->inlineSelect($this->names[$j], $this->choices[$j], $this->choicesid[$j], $this->values[$j][$i], true);
                    }
                    else if($this->types[$j] == "text"){
                        $html .= $formHtml->inlineText($this->names[$j], $this->values[$j][$i], false, true);
                    }
                    else if($this->types[$j] == "number"){
                        $html .= $formHtml->inlineNumber($this->names[$j], $this->values[$j][$i], false, true);
                    }
                    else if($this->types[$j] == "hidden"){
                        $html .= $formHtml->inlineHidden($this->names[$j], $this->values[$j][$i], false, true);
                    }
                    $html .= "</td>";
                }
                $html .= "</tr>";
            }
        } else {
            $html .= "<tr>";
            $html .= "<td><input type=\"checkbox\" name=\"chk\"/></td>";
            for ($j = 0; $j < count($this->names); $j++) {
                $html .= "<td>";
                if ($this->types[$j] == "select") {
                    $html .= $formHtml->inlineSelect($this->names[$j], $this->choices[$j], $this->choicesid[$j], "", true);
                }
                else if($this->types[$j] == "text"){
                    $html .= $formHtml->inlineText($this->names[$j], "", false, true);
                }
                else if($this->types[$j] == "number"){
                    $html .= $formHtml->inlineNumber($this->names[$j], "", false, true);
                }
                else if($this->types[$j] == "hidden"){
                    $html .= $formHtml->inlineHidden($this->names[$j], "", false, true);
                }
                $html .= "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</tbody>";
        $html .= "</table>";
        $html .= "<div class=\"col-md-6\">";
        $html .= "<input type=\"button\" class=\"btn btn-xs btn-default\" value=\" " . $this->addButtonName . " \" onclick=\"addRow('dataTable')\"/>";
        $html .= "<input type=\"button\" class=\"btn btn-xs btn-default\" value=\"" . $this->removeButtonName . "\" onclick=\"deleteRow('dataTable')\"/>";
        $html .= "<br>";
        $html .= "</div>";
        
            $html .= "</div>";
            $html .= "</div>";
        
        return $html;
    }

    public function getJavascript() {
        return file_get_contents('Framework/formadd_script.php');
    }

}
