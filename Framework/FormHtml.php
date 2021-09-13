<?php

/**
 * Class allowing to generate and check a form html view. 
 * 
 * @author Sylvain Prigent
 */
class FormHtml {

    static public function title($title, $subtitle = "", $titlelevel = 1) {
        $html = "";
        if ($title != "") {
            $html .= "<div class=\"page-header\">";
            $html .= "<h" . $titlelevel . ">" . $title;
            if ($subtitle != "") {
                $html .= "<br/><small>" . $subtitle . "</small>";
            }
            $html .= "</h" . $titlelevel . ">";
            $html .= "</div>";
        }
        return $html;
    }

    /**
     * 
     * @param type $errorMessage Text of error message
     * @return string HTML for error message
     */
    static public function errorMessage($errorMessage) {
        $html = "";
        if ($errorMessage != "") {
            $html .= "<div class=\"alert alert-danger text-center\">";
            $html .= "<p>" . $errorMessage . "</p>";
            $html .= "</div>";
        }
        return $html;
    }

    /**
     * 
     * @param type $id
     * @return string HTML of the form ID input
     */
    static public function id($id) {
        return "<input class=\"form-control\" type=\"hidden\" name=\"formid\" value=\"" . $id . "\" />";
    }

    /**
     * 
     * @param type $validationURL Validation URL
     * @param type $id ID of the form
     * @param type $useDownload True if the form use an upload input
     * @return string Header of the form
     */
    static public function formHeader($validationURL, $id, $useDownload = false) {
        if (!$useDownload) {
            $html = "<form role=\"form\" id=\"" . $id . "\" class=\"form-horizontal\" action=\"" . $validationURL . "\" method=\"POST\">";
        } else {
            $html = "<form role=\"form\" id=\"" . $id . "\" class=\"form-horizontal\" action=\"" . $validationURL . "\" method=\"POST\" enctype=\"multipart/form-data\">";
        }
        return $html;
    }

    /**
     * 
     * @return string Form HTML footer
     */
    static public function formFooter() {
        return "</form>";
    }

    /**
     * Add a separator
     * @param type $name Title of the separator
     * @param type $level Html 'h' level
     * @return string Html code
     */
    static public function separator($name, $level = 3) {
        $html = "<div class=\"page-header\">";
        $html .= "<h" . $level . ">" . $name . "</h" . $level . ">";
        $html .= "</div>";
        return $html;
    }

    /**
     * Comment in the form
     * @param type $name Text to display
     * @return string HTML code
     */
    static public function comment($name, $labelWidth, $inputWidth) {
        $html = "<div class=\"form-group" . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<p>" . $name . "</p>";
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * Hidden input
     * @param type $name Field name
     * @param type $value Field value
     * @param type $required id field required
     * @return string HTML code
     */
    static public function hidden($name, $value, $required) {
        $html = "<input class=\"form-control\" type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        return $html;
    }

    /**
     * hidden field for inline form
     * @param type $name Field name
     * @param type $value Field value
     * @param type $required is required field
     * @param type $vect Can content vectorial data
     * @return string HTML code
     */
    static public function inlinehidden($name, $value, $required = false, $vect = false) {
        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }
        $html = "<input class=\"form-control\" type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        return $html;
    }

    /**
     * 
     * @param type $validated
     * @param type $label
     * @param type $name
     * @param type $value
     * @param type $enabled
     * @param type $required
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    // #105: add readonly
    static public function text($validated, $label, $name, $value, $enabled, $required = false, $labelWidth = 2, $inputWidth = 9, $readonly = false) {
        $reqTxt = "";
        if ($required) {
            $reqTxt = "*";
        }
        
        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . $reqTxt . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"text\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\" " . $required . " " . $enabled;
        // #105: add readonly
        if ($readonly) {
            $html .= " readonly";
        }
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $name
     * @param type $value
     * @param type $required
     * @param type $vect
     * @return string
     */
    static public function inlineText($name, $value, $required = false, $vect = false) {

        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }
        $html = "<input class=\"form-control\" type=\"text\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\"";
        $html .= " value=\"" . $value . "\"" . $required . " ";
        $html .= "/>";

        return $html;
    }

    /**
     * 
     * @param type $validated
     * @param type $label
     * @param type $name
     * @param type $value
     * @param type $enabled
     * @param type $required
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function password($validated, $label, $name, $value, $enabled, $required = false, $labelWidth = 2, $inputWidth = 9) {

        $reqTxt = "";
        if ($required) {
            $reqTxt = "*";
        }

        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . $reqTxt . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"password\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required . " " . $enabled;
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $validated
     * @param type $label
     * @param type $name
     * @param type $value
     * @param type $lang
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function date($validated, $label, $name, $value, $lang, $required, $labelWidth = 2, $inputWidth = 9) {

        $star = "";
        if ($required != "") {
            $star = "*";
        }

        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . $star . "</label>";

        $html .= "<div class='col-xs-" . $inputWidth . "'>";
        $html .= "<div class='col-xs-12 input-group date form_date_" . $lang . "'>";
        $html .= "<input type='text' " . $required . " class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>";
        $html .= "          <span class=\"input-group-addon\">";
        $html .= "          <span class=\"glyphicon glyphicon-calendar\"></span>";
        $html .= "          </span>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $name
     * @param type $value
     * @param type $vect
     * @param type $lang
     * @return string
     */
    static public function inlineDate($name, $value, $vect = false, $lang = "en") {

        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }

        $html = "<div class='col-xs-12 input-group date form_date_" . $lang . "'>";
        $html .= "<input id=\"date-daily\" type='text' class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\" value=\"" . $value . "\"/>";
        $html .= "          <span class=\"input-group-addon\">";
        $html .= "          <span class=\"glyphicon glyphicon-calendar\"></span>";
        $html .= "          </span>";
        $html .= "</div>";
        $html .= "</div>";
        /*
          $html = "<div class='col-xs-12 input-group date form_date_" . $lang . "'>";
          $html .= "<input id=\"date-daily\" type='text' class=\"form-control\" name=\"" . $name . $vectv . "\" value=\"" . $value . "\"/>";
          $html .= "          <span class=\"input-group-addon\">";
          $html .= "          <span class=\"glyphicon glyphicon-calendar\"></span>";
          $html .= "          </span>";
          $html .= "</div>";
         */

        return $html;
    }

    /**
     * 
     * @param type $validated
     * @param type $label
     * @param type $name
     * @param type $value
     * @param type $lang
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function hour($validated, $label, $name, $value, $lang, $labelWidth = 2, $inputWidth = 9) {

        //echo "hours values html = ";
        //print_r($value); echo "<br/>";
        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class='col-xs-" . $inputWidth . "'>";

        $html .= "<div class=\"form-group row\">";
        $html .= "<div class=\"col-md-5\">";
        $html .= "<input class=\"form-control\" type=\"number\" min=\"0\" max=\"23\" id=\"" . $name . "H" . "\" name=\"" . $name . "H" . "\"" . " value=\"" . $value[0] . "\"" . "/>";
        $html .= "</div><div class=\"col-md-1\">";
        $html .= ":";
        $html .= "</div><div class=\"col-md-5\">";
        $html .= "<input class=\"form-control\" type=\"number\" min=\"0\" max=\"59\" id=\"" . $name . "m" . "\" name=\"" . $name . "m" . "\"" . " value=\"" . $value[1] . "\"" . "/>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    static public function datetime($validated, $label, $name, $value, $lang, $labelWidth = 2, $inputWidth = 9) {

        //echo "hours values html = ";
        //print_r($value); echo "<br/>";
        $html = "<div class=\"col-xs-12\">";
        $html .= "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class='col-xs-" . $inputWidth . "'>";

        $html .= "<div class=\"form-group row\">";

        $html .= "<div class=\"col-md-7\">";
        $html .= "<div class='input-group date form_date_" . $lang . "'>";
        $html .= "<input type='text' class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value[0] . "\"/>";
        $html .= "          <span class=\"input-group-addon\">";
        $html .= "          <span class=\"glyphicon glyphicon-calendar\"></span>";
        $html .= "          </span>";
        $html .= "</div>";
        $html .= "</div>";

        $html .= "<div class=\"col-md-2\">";
        $html .= "<input class=\"form-control\" type=\"number\" id=\"" . $name . "H" . "\" name=\"" . $name . "H" . "\"" . " value=\"" . $value[1] . "\"" . "/>";
        $html .= "</div><div class=\"col-md-1\">";
        $html .= ":";
        $html .= "</div><div class=\"col-md-2\">";
        $html .= "<input class=\"form-control\" type=\"number\" id=\"" . $name . "m" . "\" name=\"" . $name . "m" . "\"" . " value=\"" . $value[2] . "\"" . "/>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $validated
     * @param type $label
     * @param type $name
     * @param type $value
     * @param type $required
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function color($validated, $label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group" . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"color\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $validated
     * @param type $label
     * @param type $name
     * @param type $value
     * @param type $required
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function email($validated, $label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9, $checkUnicity = false) {
        $reqTxt = "";
        if ($required) {
            $reqTxt = "*";
        }
        
        
        $html = "<div class=\"form-group " . $validated . "\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . $reqTxt . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"email\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        if ($checkUnicity) {
            $html .= "onblur=\"checkEmailUnicity() \"";
        }
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $label
     * @param type $name
     * @param type $value
     * @param type $required
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function number($label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9) {

        $reqTxt = "";
        if ($required) {
            $reqTxt = "*";
        }

        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . $reqTxt . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"number\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $name
     * @param type $value
     * @param type $required
     * @param type $vect
     * @return string
     */
    static public function inlineNumber($name, $value, $required = false, $vect = false) {

        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }
        $html = "<input class=\"form-control\" type=\"number\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\"";
        $html .= " value=\"" . $value . "\"" . $required . " ";
        $html .= "/>";

        return $html;
    }

        /**
     * 
     * @param type $name
     * @param type $value
     * @param type $required
     * @param type $vect
     * @return string
     */
    static public function inlineLabel($name, $value, $vect = false) {

        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }
        $html = "<span class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\"";
        $html .= ">" . $value . "</span>";

        return $html;
    }
    /**
     * 
     * @param type $useJavascript
     * @param type $label
     * @param type $name
     * @param type $value
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function textarea($useJavascript, $label, $name, $value, $labelWidth = 2, $inputWidth = 9) {
        $divid = "";
        if ($useJavascript) {
            $divid = "id='editor'";
        }
        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= "<textarea " . $divid . " class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . "\">" . $value . "</textarea>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $label
     * @param type $name
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function upload($label, $name, $value, $labelWidth = 2, $inputWidth = 9) {
        $html = "<div class=\"form-group\"> ";
        $html .= " <label class=\"control-label col-xs-" . $labelWidth . "\"> " . $label . " </label> ";
        $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
        
        if ($value != ""){
            $html .= '<img src="'.$value.'" width="100">';
        }
        $html .= " <input type=\"file\" name=\"" . $name . "\" id=\"" . $name . "\"> ";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $label
     * @param type $name
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function downloadbutton($formid, $label, $name, $value, $manual = false, $labelWidth = 2, $inputWidth = 9) {

        if ($manual) {
            $html = "<div class=\"form-group\">";
            $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . "</label>";
            $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
            $html .= "<input name=\"".$name."\" type=\"hidden\" value=\"".$value."\">";
            $html .= "<input type=\"submit\" id=\"" . $formid . "submit" . "\" class=\"btn btn-default\" value=\"" . $label . "\" />";
            $html .= "</div>";
            $html .= "</div>";
        } else {
            
            $html = "<form role=\"form\" id=\"" . $formid . "filetransfert\" class=\"form-horizontal\" action=\"transfersimplefiledownload\" method=\"POST\">";
            $html .= "<div class=\"form-group\">";
            $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . "</label>";
            $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
            $html .= "<input name=\"filetransferurl\" type=\"hidden\" value=\"".$value."\">";
            $html .= "<input type=\"submit\" id=\"" . $formid . "filetransfertsubmit" . "\" class=\"btn btn-default\" value=\"" . $label . "\" />";
            $html .= "</div>";
            $html .= "</div>";
            $html .= "</form>";
            
            
            /*
            $html = "<div class=\"form-group\">";
            $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
            $html .= "<div class=\"col-xs-" . $inputWidth . "\">";
            $html .= "<button class=\"btn  btn-default\" id=\"" . $name . "\" type=\"button\" onclick=\"location.href = './" . $name . "'\">" . $label . "</button>";
            $html .= "</div>";
            $html .= "</div>";
             * 
             */
        }

        return $html;
    }

    /**
     * 
     * @param string $name
     * @param array $choices
     * @param array $choicesid
     * @param string $value
     * @param bool $vect
     * @param bool $submitOnchange
     * @return string
     */
    static public function inlineSelect($name, $choices, $choicesid, $value, $isMandatory, $vect = false, $submitOnchange = "") {

        $required = "";
        if ($isMandatory) {
            $required = "required";
        }

        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }
        $submit = "";
        if ($submitOnchange != "") {
            $submit = "onchange=\"updateResponsibe(this);\"";
        }
        $html = "<select " . $required . " class=\"form-control select\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\" " . $submit . " style=\"width: 100%;\">";
        for ($v = 0; $v < count($choices); $v++) {
            $selected = "";
            if ($value == $choicesid[$v]) {
                $selected = "selected=\"selected\"";
            }
            $html .= "<OPTION value=\"" . $choicesid[$v] . "\" " . $selected . ">" . $choices[$v] . "</OPTION>";
        }
        $html .= "</select>";
        if ($submitOnchange != "") {
            $html .= "<script type=\"text/javascript\">
    				function updateResponsibe(sel) {
    					$( \"#" . $submitOnchange . "\" ).submit();
    				}
				</script>";
        }
        return $html;
    }

    /**
     * 
     * @param type $label
     * @param type $name
     * @param type $choices
     * @param type $choicesid
     * @param type $value
     * @param type $labelWidth
     * @param type $inputWidth
     * @param type $submitOnChange
     * @return string
     */
    static public function select($label, $name, $choices, $choicesid, $value, $isMandatory, $labelWidth = 2, $inputWidth = 9, $submitOnChange = "") {

        $star = "";
        if ($isMandatory) {
            $star = "*";
        }

        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . $star . "</label>";
        $html .= "	<div class=\"col-xs-" . $inputWidth . "\">";
        $html .= FormHtml::inlineSelect($name, $choices, $choicesid, $value, $isMandatory, false, $submitOnChange);
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $label
     * @param type $choices
     * @param type $choicesid
     * @param type $values
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    static public function choicesList($label, $choices, $choicesid, $values, $labelWidth, $inputWidth) {
        $html = "<div class=\"form-group\">";
        $html .= "<label class=\"control-label col-xs-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "	<div class=\"col-xs-" . $inputWidth . "\">";
        for ($i = 0; $i < count($choices); $i++) {

            $html .= "<div class=\"checkbox\"> ";
            $html .= "<label> ";
            $checked = "";
            if ($values[$i] == 1) {
                $checked = "checked";
            }
            $html .= "<input type=\"checkbox\" name=\"" . $choicesid[$i] . "\"" . $checked . ">" . $choices[$i] . " ";
            $html .= "</label> ";
            $html .= "</div>";
        }
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $validationURL
     * @param type $validationButtonName
     * @param type $cancelURL
     * @param type $cancelButtonName
     * @param type $deleteURL
     * @param type $deleteID
     * @param type $deleteButtonName
     * @param type $externalButtons
     * @param type $buttonsWidth
     * @param type $buttonsOffset
     * @return string
     */
    static public function buttons($formid, $validationButtonName, $cancelURL, $cancelButtonName, $deleteURL, $deleteID, $deleteButtonName, $externalButtons = array(), $buttonsWidth = 2, $buttonsOffset = 9) {
        $html = "<div class=\"col-xs-" . $buttonsWidth . " col-xs-offset-" . $buttonsOffset . "\">";
        if ($validationButtonName != "") {
            $html .= "<input type=\"submit\" id=\"" . $formid . "submit" . "\" class=\"btn btn-primary\" value=\"" . $validationButtonName . "\" />";
        }
        if ($cancelURL != "") {
            $html .= "<button type=\"button\" onclick=\"location.href='" . $cancelURL . "'\" class=\"btn btn-default\">" . $cancelButtonName . "</button>";
        }
        if ($deleteURL != "") {
            $onclickdelete = "";
            if ($deleteURL != "") {
                $onclickdelete = "onclick=\"location.href='" . $deleteURL . "/" . $deleteID . "'\"";
            }
            $html .= "<button type=\"button\" id=\"" . $formid . "delete" . "\" " . $onclickdelete . " class=\"btn btn-danger\">" . $deleteButtonName . "</button>";
        }
        foreach ($externalButtons as $ext) {
            if ($ext["newtab"]) {
                $html .= "<input type=\"button\" value=\"" . $ext["name"] . "\" onclick=\"window.open('" . $ext["url"] . "')\" class=\"btn btn-" . $ext["type"] . "\" />";
            } else {
                $html .= "<button type=\"button\" onclick=\"location.href='" . $ext["url"] . "'\" class=\"btn btn-" . $ext["type"] . "\">" . $ext["name"] . "</button>";
            }
        }

        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @return type
     */
    static public function timePickerScript() {
        return file_get_contents("Framework/timepicker_script.php");
    }

    /**
     * 
     * @return type
     */
    static public function textAreaScript() {
        return file_get_contents("Framework/textarea_script.php");
    }

    static public function ajaxScript($formId, $validationURL) {
        $string = file_get_contents('Framework/formajax_script.php');
        $string1 = str_replace("formid", $formId, $string);
        return str_replace("validationurl", $validationURL, $string1);
    }

    /**
     * 
     * @return type
     */
    static public function checkUnicityScript() {
        return file_get_contents("Framework/formUtils.php");
    }

}
