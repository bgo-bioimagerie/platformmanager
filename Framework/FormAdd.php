<?php

require_once 'Framework/Form.php';

class FormAdd
{
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

    public function __construct(Request $request, $id)
    {
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
    public function setButtonsNames($addButtonName, $removeButtonName)
    {
        $this->addButtonName = $addButtonName;
        $this->removeButtonName = $removeButtonName;
    }

    public function setButtonsVisible($visible)
    {
        $this->buttonsVisible = $visible;
    }

    /**
     * Set content values
     * @param type $name content name
     * @param type $value content value
     */
    protected function setValue($name, $value)
    {
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
    public function addHidden($name, $values)
    {
        $this->types[] = "hidden";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

     /**
     * Add label field
     * @param type $name Field name
     * @param type $values Field value
     */
    public function addLabel($name, $values)
    {
        $this->types[] = "label";
        $this->names[] = $name;
        $this->labels[] = $name;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

    /**
     * Add a select field
     * @param string $name Field name
     * @param string $label Field label
     * @param array $choices List of choices names
     * @param array $choicesid List of choices Ids
     * @param array $values List of default values
     */
    public function addSelect($name, $label, $choices, $choicesid, $values = array(), $isMandatory=false)
    {
        $this->types[] = "select";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = $choices;
        $this->choicesid[] = $choicesid;
    }

    /**
     * Add a text field
     * @param type $name Field name
     * @param type $label Field label
     * @param type $values Field default values
     */
    public function addText($name, $label, $values = array())
    {
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
    public function addDate($name, $label, $values = array())
    {
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
    public function addNumber($name, $label, $values = array())
    {
        $this->types[] = "number";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

         /**
     * Add number field with step="any"
     * @param type $name Field name
     * @param type $label Field label
     * @param type $values Field default values
     */
    public function addFloat($name, $label, $values = array())
    {
        $this->types[] = "float";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = "";
        $this->choicesid[] = "";
    }

    /**
     * Get formAdd Id
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param type $label Form label
     * @param type $labelWidth Bootstrap columns number for the label
     * @param type $inputWidth Bootstrap columns number for the fields
     * @return string The formAdd HTML code
     */
    public function getHtml($label = "", $labelWidth = 2, $inputWidth = 9)
    {
        $html = "";
        if ($label != "") {
            $html = "<div class=\"form-group row mb-3\">";
            $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . "</label>";
            $html .= "    <div class=\"col-12 col-md-" . $inputWidth . " table-responsive\" >";
        } else {
            $html .= "<div class=\"form-group row mb-3\">";
            $html .= "    <div class=\"col-12 table-responsive\">";
        }

        $tableID = $this->id . "table";
        $html .= "<table id=\"".$tableID."\" class=\"table table-striped\"> ";
        $html .= "<thead>";
        $html .= "<tr>";
        if ($this->buttonsVisible) {
            $html .= "<th></th>";
        }
        for ($l = 0; $l < count($this->labels); $l++) {
            if ($this->types[$l] == "hidden") {
                $html .= "<th style=\"width:0em;\"> </th>";
            } else {
                $html .= "<th>" . $this->labels[$l] . "</th>";
            }
        }

        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";

        $formHtml = new FormHtml();
        if (count($this->values[0]) > 0) {
            for ($i = 0; $i < count($this->values[0]); $i++) {
                $html .= "<tr>";
                if ($this->buttonsVisible) {
                    $html .= "<td><input type=\"checkbox\" name=\"chk\"/></td>";
                }
                for ($j = 0; $j < count($this->types); $j++) {
                    $html .= "<td>";
                    if ($this->types[$j] == "select") {
                        $html .= $formHtml->inlineSelect($this->names[$j], $this->choices[$j], $this->choicesid[$j], $this->values[$j][$i], $this->isMandatory[$j] ?? false, true);
                    } elseif ($this->types[$j] == "text") {
                        $html .= $formHtml->inlineText($this->names[$j], $this->values[$j][$i], false, true);
                    } elseif ($this->types[$j] == "textdate") {
                        $html .= $formHtml->inlineDate($this->names[$j], $this->values[$j][$i], true);
                    } elseif ($this->types[$j] == "number") {
                        $html .= $formHtml->inlineNumber($this->names[$j], $this->values[$j][$i], false, true);
                    } elseif ($this->types[$j] == "float") {
                        $html .= $formHtml->inlineNumber($this->names[$j], $this->values[$j][$i], false, true, true);
                    } elseif ($this->types[$j] == "hidden") {
                        $html .= $formHtml->inlineHidden($this->names[$j], $this->values[$j][$i], false, true);
                    } elseif ($this->types[$j] == "label") {
                        $html .= $formHtml->inlineLabel($this->names[$j], $this->values[$j][$i], true);
                    } else {
                        $html .= "error undefined form input type " . $this->types[$j];
                    }
                    $html .= "</td>";
                }
                $html .= "</tr>";
            }
        } else {
            $html .= "<tr>";
            if ($this->buttonsVisible) {
                $html .= "<td><input type=\"checkbox\" name=\"chk\"/></td>";
            }
            for ($j = 0; $j < count($this->names); $j++) {
                $html .= "<td>";
                if ($this->types[$j] == "select") {
                    $html .= $formHtml->inlineSelect($this->names[$j], $this->choices[$j], $this->choicesid[$j], "", $this->isMandatory[$j] ?? false, true);
                } elseif ($this->types[$j] == "text") {
                    $html .= $formHtml->inlineText($this->names[$j], "", false, true);
                } elseif ($this->types[$j] == "textdate") {
                    $html .= $formHtml->inlineDate($this->names[$j], "", true);
                } elseif ($this->types[$j] == "number") {
                    $html .= $formHtml->inlineNumber($this->names[$j], "", false, true);
                } elseif ($this->types[$j] == "float") {
                    $html .= $formHtml->inlineNumber($this->names[$j], "", false, true, true);
                } elseif ($this->types[$j] == "hidden") {
                    $html .= $formHtml->inlineHidden($this->names[$j], "", false, true);
                } elseif ($this->types[$j] == "label") {
                    $html .= $formHtml->inlineLabel($this->names[$j], "", true);
                }
                $html .= "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</tbody>";
        $html .= "</table>";

        if ($this->buttonsVisible) {
            $html .= "<div class=\"col-6\">";
            $html .= "<input type=\"button\" id=\"" . $this->id . "_add" . "\" class=\"m-1 btn btn-sm btn-outline-dark\" value=\" " . $this->addButtonName . " \" onclick=\"addRow('".$tableID."')\"/>";
            $html .= "<input type=\"button\" id=\"" . $this->id . "_delete" . "\"  class=\"m-1 btn btn-sm btn-outline-dark\" value=\"" . $this->removeButtonName . "\" onclick=\"deleteRow('".$tableID."')\"/>";
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
    public function getJavascript()
    {
        $tableID = $this->id . "table";
        $string =  file_get_contents('Framework/formadd_script.php');
        return str_replace("tableIDname", $tableID, $string);
    }
}
