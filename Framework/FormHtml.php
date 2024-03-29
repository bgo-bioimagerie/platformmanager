<?php

/**
 * Class allowing to generate and check a form html view.
 *
 * @author Sylvain Prigent
 */
class FormHtml
{
    public static function title($title, $subtitle = "", $titlelevel = 1)
    {
        $html = "";
        if ($title != "") {
            $html .= "<div class=\"page-header m-2\">";
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
    public static function errorMessage($errorMessage)
    {
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
    public static function id($id)
    {
        return "<input class=\"form-control\" type=\"hidden\" name=\"formid\" value=\"" . $id . "\" />";
    }

    /**
     *
     * @param type $validationURL Validation URL
     * @param type $id ID of the form
     * @param type $useDownload True if the form use an upload input
     * @return string Header of the form
     */
    public static function formHeader($validationURL, $id, $useDownload = false)
    {
        if (!$useDownload) {
            $html = "<form class=\"container\" role=\"form\" id=\"" . $id . "\"  action=\"" . $validationURL . "\" method=\"POST\">";
        } else {
            $html = "<form class=\"container\" role=\"form\" id=\"" . $id . "\"  action=\"" . $validationURL . "\" method=\"POST\" enctype=\"multipart/form-data\">";
        }
        return $html;
    }

    /**
     *
     * @return string Form HTML footer
     */
    public static function formFooter()
    {
        return "</form>";
    }

    /**
     * Add a separator
     * @param type $name Title of the separator
     * @param type $level Html 'h' level
     * @return string Html code
     */
    public static function separator($name, $level = 3)
    {
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
    public static function comment($name, $labelWidth, $inputWidth)
    {
        $html = "<div class=\"mb-3 row" . "\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . "</label>";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";
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
    public static function hidden($name, $value, $required)
    {
        $html = "<div class=\"form-group\" id=\"form_blk_$name\">";
        $html .= "<input class=\"form-control\" type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
        $html .= "/>";
        $html .= "</div>";
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
    public static function inlinehidden($name, $value, $required = false, $vect = false)
    {
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
     * @param bool $readonly
     * @param bool $checkUnicity
     * @return string
     */
    // #105: add readonly
    public static function text($validated, $label, $name, $value, $enabled, $required = false, $labelWidth = 2, $inputWidth = 9, $readonly = false, $checkUnicity = false)
    {
        $reqTxt = "";
        if ($required) {
            $reqTxt = "*";
        }

        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row" . $validated . "\">";
        $html .= "<label class=\"col-$labelWidth col-form-label\">" . $label . $reqTxt . "</label>";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control";
        if ($checkUnicity) {
            $html .= " unique";
        }
        $html .= "\"";
        $html .= " type=\"text\" id=\"" . $name . "\" name=\"" . $name . "\"";
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
    public static function inlineText($name, $value, $required = false, $vect = false)
    {
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
    public static function password($validated, $label, $name, $value, $enabled, $required = false, $labelWidth = 2, $inputWidth = 9)
    {
        $reqTxt = "";
        if ($required) {
            $reqTxt = "*";
        }

        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row" . $validated . "\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . $reqTxt . "</label>";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";
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
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    public static function date($validated, $label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9)
    {
        $star = "";
        if ($required != "") {
            $star = "*";
        }

        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row" . $validated . "\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . $star . "</label>";

        $html .= "<div class='col-12 col-md-" . $inputWidth . "'>";
        $html .= "<div class='col-12 input-group date'>";
        $html .= "<input type='date' " . $required . " class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>";
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
    public static function inlineDate($name, $value, $vect = false)
    {
        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }

        $html = "<div id=\"form_blk_$name\" class='col-12 input-group date'>";
        $html .= "<input id=\"date-daily\" type='date' class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\" value=\"" . $value . "\"/>";
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
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    public static function hour($validated, $label, $name, $value, $labelWidth = 2, $inputWidth = 9)
    {
        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row" . $validated . "\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class='col-12 col-md-" . $inputWidth . "'>";

        $html .= "<div class=\"row\">";
        $html .= "<div class=\"col-5\">";
        $html .= "<input class=\"form-control\" type=\"number\" min=\"0\" max=\"23\" id=\"" . $name . "H" . "\" name=\"" . $name . "H" . "\"" . " value=\"" . $value[0] . "\"" . "/>";
        $html .= "</div><div class=\"col-1\">";
        $html .= ":";
        $html .= "</div><div class=\"col-5\">";
        $html .= "<input class=\"form-control\" type=\"number\" min=\"0\" max=\"59\" id=\"" . $name . "m" . "\" name=\"" . $name . "m" . "\"" . " value=\"" . $value[1] . "\"" . "/>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    public static function datetime($validated, $label, $name, $value, $labelWidth = 2, $inputWidth = 9)
    {
        $html = "<div id=\"form_blk_$name\" class=\"col-12\">";
        $html .= "<div class=\"mb-3 row" . $validated . "\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class='col-12 col-md-" . $inputWidth . "'>";

        $html .= "<div class=\"row\">";

        $html .= "<div class=\"col-7\">";
        $html .= "<input type='date' class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value[0] . "\"/>";
        $html .= "</div>";

        $html .= "<div class=\"col-2\">";
        $html .= "<input class=\"form-control\" type=\"number\" id=\"" . $name . "H" . "\" name=\"" . $name . "H" . "\"" . " value=\"" . $value[1] . "\"" . "/>";
        $html .= "</div><div class=\"col-1\">";
        $html .= ":";
        $html .= "</div><div class=\"col-2\">";
        $html .= "<input class=\"form-control\" type=\"number\" id=\"" . $name . "m" . "\" name=\"" . $name . "m" . "\"" . " value=\"" . $value[2] . "\"" . "/>";
        $html .= "</div>";
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
    public static function color($validated, $label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9)
    {
        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row" . $validated . "\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";
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
    public static function email($validated, $label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9, $checkUnicity = false)
    {
        $reqTxt = "";
        if ($required) {
            $reqTxt = "*";
        }


        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row " . $validated . "\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . $reqTxt . "</label>";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control";
        if ($checkUnicity) {
            $html .= " unique";
        }
        $html .= "\"";
        $html .= " type=\"email\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required;
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
    public static function number($label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9, $isFloat = false)
    {
        $reqTxt = "";
        if ($required) {
            $reqTxt = "*";
        }
        $float = $isFloat ? "step=\"any\"" : "";
        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . $reqTxt . "</label>";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";
        $html .= "<input class=\"form-control\" type=\"number\" id=\"" . $name . "\" name=\"" . $name . "\"";
        $html .= " value=\"" . $value . "\"" . $required . " " . $float;
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
    public static function inlineNumber($name, $value, $required = false, $vect = false, $isFloat = false)
    {
        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }
        $float = $isFloat ? "step=\"any\"" : "";
        $html = "<input class=\"form-control\" type=\"number\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\"";
        $html .= " value=\"" . $value . "\"" . $required . " " . $float;
        $html .= "/>";

        return $html;
    }

        /**
     *
     * @param type $name
     * @param type $value
     * @param type $vect
     * @return string
     */
    public static function inlineLabel($name, $value, $vect = false)
    {
        $vectv = "";
        if ($vect) {
            $vectv = "[]";
        }
        $html = "<label class=\"col-form-label\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\"";
        $html .= ">" . $value . "</label>";
        return $html;
    }
    /**
     *
     * @param type $useJavascript
     * @param type $label
     * @param type $name
     * @param type $value
     * @param bool $required is mandatory?
     * @param type $labelWidth
     * @param type $inputWidth
     * @return string
     */
    public static function textarea($useJavascript, $label, $name, $value, $required, $labelWidth = 2, $inputWidth = 9)
    {
        $divid = "";
        if ($useJavascript) {
            $divid = "id='rtxt_$name'";
        }
        $mandatory = '';
        if ($required) {
            $label = $label.'*';
            $mandatory = ' required="required" ';
        }
        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";
        $html .= "<textarea " . $divid . " class=\"form-control\" id=\"" . $name . "\" name=\"" . $name . "\"".$mandatory.">" . $value . "</textarea>";
        $html .= "</div>";
        $html .= "</div>";
        if ($useJavascript) {
            $html .=  '<script>
    ClassicEditor
    .create( document.querySelector( \'#rtxt_'.$name.'\' ) )
    .catch( error => {
        console.error( error );
    } );
</script>';
        }
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
    public static function upload($label, $name, $value, $labelWidth = 2, $inputWidth = 9)
    {
        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row\"> ";
        $html .= " <label class=\"col-form-label col-12 col-md-" . $labelWidth . "\"> " . $label . " </label> ";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";

        if ($value != "") {
            $html .= '<img src="'.$value.'" width="100">';
        }
        $html .= " <input class=\"form-control\" type=\"file\" name=\"" . $name . "\" id=\"" . $name . "\"> ";
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
    public static function downloadbutton($formid, $label, $name, $value, $labelWidth = 2, $inputWidth = 9)
    {
        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . "</label>";
        $html .= "<div class=\"col-12 col-md-" . $inputWidth . "\">";
        $html .= "<input name=\"".$name."\" type=\"hidden\" value=\"".$value."\">";
        $html .= "<input type=\"submit\" id=\"" . $formid . "submit" . "\" class=\"btn btn-outline-dark\" value=\"" . $label . "\" />";
        $html .= "</div>";
        $html .= "</div>";

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
    public static function inlineSelect($name, $choices, $choicesid, $value, $isMandatory, $vect = false, $submitOnchange = "")
    {
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
        $html = "<select " . $required . " class=\"form-select\" id=\"" . $name . "\" name=\"" . $name . $vectv . "\" " . $submit . " style=\"width: 100%;\">";
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
    public static function select($label, $name, $choices, $choicesid, $value, $isMandatory, $labelWidth = 2, $inputWidth = 9, $submitOnChange = "")
    {
        $star = "";
        if ($isMandatory) {
            $star = "*";
        }

        $html = "<div id=\"form_blk_$name\" class=\"mb-3 row\">";
        $html .= "<label class=\"col-form-label col-12 col-md-" . $labelWidth . "\">" . $label . $star . "</label>";
        $html .= "    <div class=\"col-12 col-md-" . $inputWidth . "\">";
        $html .= FormHtml::inlineSelect($name, $choices, $choicesid, $value, $isMandatory, false, $submitOnChange);
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     *
     * @param string $label
     * @param array $choices
     * @param array $choicesid
     * @param array $values
     * @param int $labelWidth
     * @param int $inputWidth
     * @return string
     */
    public static function choicesList($label, $choices, $choicesid, $values, $labelWidth, $inputWidth)
    {
        $html = "<div class=\"mb-3 row\">";
        $html .= "<label class=\"form-check-label col-12 col-md-" . $labelWidth . "\">" . $label . "</label>";
        $html .= "    <div class=\"col-12 col-md-" . $inputWidth . "\">";
        for ($i = 0; $i < count($choices); $i++) {
            $html .= "<div id=\"form_blk_$choicesid[$i]\" class=\"checkbox\"> ";
            $html .= "<label> ";
            $checked = "";
            if ($values[$i] == 1) {
                $checked = "checked";
            }
            $html .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"" . $choicesid[$i] . "\"" . $checked . ">" . $choices[$i] . " ";
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
    public static function buttons($formid, $validationButtonName, $cancelURL, $cancelButtonName, $deleteURL, $deleteID, $deleteButtonName, $externalButtons = array(), $buttonsWidth = 2, $buttonsOffset = 9)
    {
        $html = '<div class="mb-3 row">';
        $html .= "<div class=\"col-12 col-md-" . $buttonsWidth . " offset-" . $buttonsOffset . "\">";
        if ($validationButtonName != "") {
            $html .= "<input type=\"submit\" id=\"" . $formid . "submit" . "\" class=\"m-2 btn btn-primary\" value=\"" . $validationButtonName . "\" />";
        }
        if ($cancelURL != "") {
            $html .= "<button type=\"button\" onclick=\"location.href='" . $cancelURL . "'\" class=\"m-2 btn btn-outline-dark\">" . $cancelButtonName . "</button>";
        }
        if ($deleteURL != "") {
            $onclickdelete = "";
            if ($deleteURL != "") {
                $onclickdelete = "onclick=\"location.href='" . $deleteURL . "/" . $deleteID . "'\"";
            }
            $html .= "<button type=\"button\" id=\"" . $formid . "delete" . "\" " . $onclickdelete . " class=\"m-2 btn btn-danger\">" . $deleteButtonName . "</button>";
        }
        foreach ($externalButtons as $ext) {
            if ($ext["newtab"]) {
                $html .= "<input type=\"button\" value=\"" . $ext["name"] . "\" onclick=\"window.open('" . $ext["url"] . "')\" class=\"m-2 btn btn-" . $ext["type"] . "\" />";
            } else {
                $html .= "<button type=\"button\" onclick=\"location.href='" . $ext["url"] . "'\" class=\"m-2 btn btn-" . $ext["type"] . "\">" . $ext["name"] . "</button>";
            }
        }
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     *
     * @return type
     */
    public static function textAreaScript()
    {
        return '<script src="/externals/ckeditor5/build/ckeditor.js"></script>';
    }

    public static function ajaxScript($formId, $validationURL)
    {
        $string = file_get_contents('Framework/formajax_script.php');
        $string1 = str_replace("formid", $formId, $string);
        return str_replace("validationurl", $validationURL, $string1);
    }

    /**
     *
     * @return type
     */
    public static function checkUnicityScript()
    {
        return file_get_contents("Framework/checkUnicity_script.php");
    }

    /**
     *
     * @return type
     */
    public static function suggestLoginScript()
    {
        return file_get_contents("Framework/suggestLogin_script.php");
    }
}
