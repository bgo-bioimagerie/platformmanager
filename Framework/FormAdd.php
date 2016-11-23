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
    protected $buttonsVisible;

    public function __construct(Request $request, $id) {
        $this->request = $request;
        $this->id = $id;
        $this->parseRequest = false;
        $this->buttonsVisible = true;
    }

    /**
     * Set the add and remove buttons names
     * @param type $addButtonName Add button name
     * @param type $removeButtonName Remove button name
     */
    public function setButtonsNames($addButtonName, $removeButtonName) {
        $this->addButtonName = $addButtonName;
        $this->removeButtonName = $removeButtonName;
    }

    public function setButtonsVisible($visible){
        $this->buttonsVisible = $visible;
    }
    
    /**
     * Set content values
     * @param type $name content name
     * @param type $value content value
     */
    protected function setValue($name, $value) {
        if ($this->parseRequest) {
            $this->values[] = $this->request->getParameterNoException($name);
        } else {
            $this->values[] = $value;
        }
    }

    /**
     * Add hidden field 
     * @param type $name Field name
     * @param type $values Field value
     */
    public function addHidden($name, $values) {
        $this->types[] = "hidden";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

    /**
     * Add a select field 
     * @param type $name Field name
     * @param type $label Field label
     * @param type $choices List of choices names
     * @param type $choicesid List of choices Ids
     * @param type $values List of default values
     */
    public function addSelect($name, $label, $choices, $choicesid, $values = array()) {
        $this->types[] = "select";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = $choices;
        $this->choicesid[] = $choicesid;
    }

    /**
     * Add a text field
     * @param type $name Field name
     * @param type $label Field label
     * @param type $values Field default values
     */
    public function addText($name, $label, $values = array()) {
        $this->types[] = "text";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

    /**
     * Add a date field
     * @param type $name Field name
     * @param type $label Field label
     * @param type $values Field default values
     */
    public function addDate($name, $label, $values = array()) {
        $this->types[] = "textdate";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

    /**
     * Add number field
     * @param type $name Field name
     * @param type $label Field label
     * @param type $values Field default values
     */
    public function addNumber($name, $label, $values = array()) {
        $this->types[] = "number";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

    /**
     * 
     * @param type $lang Interface language
     * @param type $label Form label
     * @param type $labelWidth Bootstrap columns number for the label
     * @param type $inputWidth Bootstrap columns number for the fields
     * @return string The formAdd HTML code
     */
    public function getHtml($lang = "en", $label = "", $labelWidth = 2, $inputWidth = 9) {
        //print_r($this->types);
        $html = "";
        if ($label != "") {
            $html = "<div class=\"form-group\">";
            $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
            $html .= "	<div class=\"col-xs-" . $inputWidth . "\">";
        } else {
            $html .= "<div class=\"form-group\">";
            $html .= "	<div class=\"col-xs-12\">";
        }

        $tableID = $this->id . "table";
        $html .= "<table id=\"".$tableID."\" class=\"table table-striped\"> ";
        $html .= "<thead>";
        $html .= "<tr>";
        if($this->buttonsVisible){
            $html .= "<th></th>";
        }
        for ($l = 0; $l < count($this->labels); $l++) {
            if ($this->types[$l] == "hidden") {
                $html .= "<th style=\"width:0em;\"> </th>";
            } else {
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
                if($this->buttonsVisible){
                    $html .= "<td><input type=\"checkbox\" name=\"chk\"/></td>";
                }
                for ($j = 0; $j < count($this->types); $j++) {
                    $html .= "<td>";

                    if ($this->types[$j] == "select") {
                        $html .= $formHtml->inlineSelect($this->names[$j], $this->choices[$j], $this->choicesid[$j], $this->values[$j][$i], true);
                    } else if ($this->types[$j] == "text") {
                        $html .= $formHtml->inlineText($this->names[$j], $this->values[$j][$i], false, true);
                    } else if ($this->types[$j] == "textdate") {

                        $html .= $formHtml->inlineDate($this->names[$j], $this->values[$j][$i], true, $lang);
                    } else if ($this->types[$j] == "number") {
                        $html .= $formHtml->inlineNumber($this->names[$j], $this->values[$j][$i], false, true);
                    } else if ($this->types[$j] == "hidden") {
                        $html .= $formHtml->inlineHidden($this->names[$j], $this->values[$j][$i], false, true);
                    } else {
                        $html .= "error undefine form input type " . $this->types[$j];
                    }
                    $html .= "</td>";
                }
                $html .= "</tr>";
            }
        } else {
            $html .= "<tr>";
            if($this->buttonsVisible){
                $html .= "<td><input type=\"checkbox\" name=\"chk\"/></td>";
            }
            for ($j = 0; $j < count($this->names); $j++) {
                $html .= "<td>";
                if ($this->types[$j] == "select") {
                    $html .= $formHtml->inlineSelect($this->names[$j], $this->choices[$j], $this->choicesid[$j], "", true);
                } else if ($this->types[$j] == "text") {
                    $html .= $formHtml->inlineText($this->names[$j], "", false, true);
                } else if ($this->types[$j] == "textdate") {
                    $html .= $formHtml->inlineDate($this->names[$j], "", true, $lang);
                } else if ($this->types[$j] == "number") {
                    $html .= $formHtml->inlineNumber($this->names[$j], "", false, true);
                } else if ($this->types[$j] == "hidden") {
                    $html .= $formHtml->inlineHidden($this->names[$j], "", false, true);
                }
                $html .= "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</tbody>";
        $html .= "</table>";
        
        if($this->buttonsVisible){
            $html .= "<div class=\"col-md-6\">";
            $html .= "<input type=\"button\" class=\"btn btn-xs btn-default\" value=\" " . $this->addButtonName . " \" onclick=\"addRow('".$tableID."')\"/>";
            $html .= "<input type=\"button\" class=\"btn btn-xs btn-default\" value=\"" . $this->removeButtonName . "\" onclick=\"deleteRow('".$tableID."')\"/>";
            $html .= "<br>";
            $html .= "</div>";
        }

        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * 
     * @return string FormAdd Javascript content
     */
    public function getJavascript() {
        $tableID = $this->id . "table";
        $string =  file_get_contents('Framework/formadd_script.php');
        return str_replace("tableIDname", $tableID, $string);
    }

}
