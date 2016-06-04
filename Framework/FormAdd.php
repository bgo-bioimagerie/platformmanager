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

    public function addSelect($name, $label, $choices, $choicesid, $values = array()) {
        $this->types[] = "select";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($name, $values);
        $this->isMandatory[] = false;
        $this->choices[] = $choices;
        $this->choicesid[] = $choicesid;
    }

    public function getHtml($lang = "en") {

        $html = "<table id=\"dataTable\" class=\"table table-striped\"> ";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th></th>";
        foreach ($this->labels as $label) {
            $html .= "<th style=\"min-width:10em;\">" . $label . "</th>";
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
                $html .= "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</tbody>";
        $html .= "</table>";
        $html .= "<div class=\"col-md-6\">";
        $html .= "<input type=\"button\" class=\"btn btn-default\" value=\" " . $this->addButtonName . " \" onclick=\"addRow('dataTable')\"/>";
        $html .= "<input type=\"button\" class=\"btn btn-default\" value=\"" . $this->removeButtonName . "\" onclick=\"deleteRow('dataTable')\"/>";
        $html .= "<br>";
        $html .= "</div>";
        return $html;
    }

    public function getJavascript() {
        return file_get_contents('Framework/formadd_script.php');
    }

}
