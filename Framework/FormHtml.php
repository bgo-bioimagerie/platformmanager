<?php

/**
 * Class allowing to generate and check a form html view. 
 * 
 * @author Sylvain Prigent
 */
class FormHtml {

    static public function title($title, $subtitle = "") {
        $html = "";
        if ($title != "") {
            $html .= "<div class=\"page-header\">";
            $html .= "<h1>" . $title;
            if ($subtitle != "") {
                $html .= "<br/><small>" . $subtitle . "</small>";
            }
            $html .= "</h1>";
            $html .= "</div>";
        }
        return $html;
    }

    static public function errorMessage($errorMessage) {
        $html = "";
        if ($errorMessage != "") {
            $html .= "<div class=\"alert alert-danger text-center\">";
            $html .= "<p>" . $errorMessage . "</p>";
            $html .= "</div>";
        }
        return $html;
    }

    static public function id($id) {
        return "<input class=\"form-control\" type=\"hidden\" name=\"formid\" value=\"" . $id . "\" />";
    }

    static public function formHeader($validationURL, $useDownload = false) {
        if (!$useDownload) {
            $html = "<form role=\"form\" class=\"form-horizontal\" action=\"" . $validationURL . "\" method=\"post\">";
        } else {
            $html = "<form role=\"form\" class=\"form-horizontal\" action=\"" . $validationURL . "\" method=\"post\" enctype=\"multipart/form-data\">";
        }
        return $html;
    }

    static public function formFooter() {
        return "</form>";
    }

    static public function separator($name, $level = 3) {
        $html = "<div class=\"page-header\">";
        $html .= "<h" . $level . ">" . $name . "</h" . $level . ">";
        $html .= "</div>";
        return $html;
    }

    static public function comment($name) {
        $html = "<div >";
        $html .= "<p>" . $name . "</p>";
        $html .= "</div>";
        return $html;
    }

    static public function hidden($name, $value, $required) {
        $html = "<input class=\"form-control\" type=\"hidden\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        return $html;
    }

    static public function text($validated, $label, $name, $value, $enabled, $required = false, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"text\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required . " " . $enabled;
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function password($validated, $label, $name, $value, $enabled, $required = false, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"password\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required . " " . $enabled;
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function date($validated, $label, $name, $value, $lang, $labelWidth = 2, $inputWidth = 9) {

        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";

        $html .= "<div class='col-xs-" . $inputWidth . "'>";
        $html .= "<div class='col-xs-12 input-group date form_date_" . $lang . "'>";
        $html .= "<input id=\"date-daily\" type='text' class=\"form-control\" name=\"" . $name . "\" value=\"" . $value . "\"/>";
        $html .= "          <span class=\"input-group-addon\">";
        $html .= "          <span class=\"glyphicon glyphicon-calendar\"></span>";
        $html .= "          </span>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function color($validated, $label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"color\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function email($validated, $label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group " . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"text\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function number($label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"number\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function textarea($useJavascript, $label, $name, $value, $labelWidth = 2, $inputWidth = 9) {
        $divid = "";
        if ($useJavascript) {
            $divid = "id='editor'";
        }
        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<textarea " . $divid . " class=\"form-control\" name=\"" . $name . "\">" . $value . "</textarea>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function download($label, $name, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= " <input type=\"file\" name=\"" . $name . "\" id=\"" . $name . "\"> ";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function downloadbutton($label, $name, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<button class=\"btn  btn-default\" type=\"button\" onclick=\"location.href = '" . $name . "'\">" . $label . "</button>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function inlineSelect($name, $choices, $choicesid, $value, $vect = false){
        
        $vectv = "";
        if ($vect){
            $vectv = "[]";
        }
        $html = "<select class=\"form-control\" name=\"" . $name . $vectv . "\">";
        for ($v = 0; $v < count($choices); $v++) {
            $selected = "";
            if ($value == $choicesid[$v]) {
                $selected = "selected=\"selected\"";
            }
            $html .= "<OPTION value=\"" . $choicesid[$v] . "\"" . $selected . ">" . $choices[$v] . "</OPTION>";
        }
        $html .= "</select>";
        return $html;
    }
    
    static public function select($label, $name, $choices, $choicesid, $value, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "	<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= FormHtml::inlineSelect($name, $choices, $choicesid, $value);
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function buttons($validationURL, $validationButtonName, $cancelURL, $cancelButtonName, $deleteURL, $deleteID, $deleteButtonName, $buttonsWidth = 2, $buttonsOffset = 9) {
        $html = "<div class=\"col-xs-" . $buttonsWidth . " col-xs-offset-" . $buttonsOffset . "\">";
        if ($validationURL != "") {
            $html .= "<input type=\"submit\" class=\"btn btn-primary\" value=\"" . $validationButtonName . "\" />";
        }
        if ($cancelURL != "") {
            $html .= "<button type=\"button\" onclick=\"location.href='" . $cancelURL . "'\" class=\"btn btn-default\">" . $cancelButtonName . "</button>";
        }
        if ($deleteURL != "") {
            $html .= "<button type=\"button\" onclick=\"location.href='" . $deleteURL . "/" . $deleteID . "'\" class=\"btn btn-danger\">" . $deleteButtonName . "</button>";
        }
        $html .= "</div>";
        return $html;
    }

    static public function timePickerScript() {
        return file_get_contents("Framework/timepicker_script.php");
    }

    static public function textAreaScript() {
        return file_get_contents("Framework/textarea_script.php");
    }

}
