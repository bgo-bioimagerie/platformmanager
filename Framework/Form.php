<?php

require_once 'Framework/Request.php';
require_once 'Framework/FormAdd.php';
require_once 'Framework/FormHtml.php';

/**
 * Class allowing to generate and check a form html view.
 *
 * @author Sylvain Prigent
 */
class Form
{
    /** request */
    private $request;

    /** form info */
    private $title;
    private $titlelevel;
    private $subtitle;
    private $id;
    private $parseRequest;
    private $errorMessage;
    private $useAjax;

    /** form description */
    private $types;
    private $names;
    private $values;
    private $labels;
    private $isMandatory;
    private $enabled;

    private $readonly;
    private $checkUnicity;
    private $suggestLogin;

    private $choices;
    private $choicesid;
    private $validated;
    private $useJavascript;
    private $submitOnChange;

    /** Validations/cancel/delete buttons */
    private $validationButtonName;
    private $validationURL;
    private $cancelButtonName;
    private $cancelURL;
    private $deleteButtonName;
    private $deleteURL;
    private $deleteID;

    /** form display (in bootstrap column number) */
    private $labelWidth;
    private $inputWidth;
    private $buttonsWidth;
    private $buttonsOffset;

    /** for download feild */
    private $useUpload;
    private $isTextArea;
    private $formAdd;
    private $isFormAdd;
    private $externalButtons;

    /**
     * Constructor
     * @param Request $request Request that contains the post data
     * @param unknown $id Form ID
     */
    public function __construct(Request $request, $id, $useAjax = false)
    {
        $this->request = $request;
        $this->id = $id;
        $this->labelWidth = 4;
        $this->inputWidth = 6;
        $this->buttonsWidth = 12;
        $this->buttonsOffset = 1;
        $this->title = "";
        $this->validationURL = "";
        $this->cancelURL = "";
        $this->deleteURL = "";
        $this->isTextArea = false;
        $this->isFormAdd = false;
        $this->externalButtons = array();
        $this->useAjax = $useAjax;
        $this->suggestLogin = false;

        $this->parseRequest = false;
        $formID = $request->getParameterNoException("formid");
        if ($formID == $this->id) {
            $this->parseRequest = true;
        }

        $this->useUpload = false;
    }

    /**
     *
     * @param type $labelWidth Number of bootstrap columns for the form labels
     * @param type $inputWidth Number of bootstrap columns for the form fields
     */
    public function setColumnsWidth($labelWidth, $inputWidth)
    {
        $this->labelWidth = $labelWidth;
        $this->inputWidth = $inputWidth;
    }

    /**
     * Add a button in the form validation button bar
     * @param type $name Button text
     * @param type $url Action URL
     * @param type $type Bootstrap button type
     */
    public function addExternalButton($name, $url, $type = "danger", $newtab = false)
    {
        $this->externalButtons[] = array("name" => $name, "url" => $url, "type" => $type, "newtab" => $newtab);
    }

    /**
     * Set the form title
     * @param string $title Form title
     */
    public function setTitle($title, $level = 3)
    {
        $this->title = $title;
        $this->titlelevel = $level;
    }

    /**
     * Set the form sub title
     * @param string $subtitle Form sub title
     */
    public function setSubTitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * Set a validation button to the title
     * @param string $name Button text
     * @param string $url URL of the form post query
     */
    public function setValidationButton($name, $url)
    {
        $this->validationButtonName = $name;
        $this->validationURL = $url;
    }

    /**
     *
     * @param type $url URL of the validation button
     */
    public function setValisationUrl($url)
    {
        $this->validationURL = $url;
    }

    /**
     * Set a cancel button to the form
     * @param string $name Button text
     * @param string $url URL redirection
     */
    public function setCancelButton($name, $url)
    {
        $this->cancelButtonName = $name;
        $this->cancelURL = $url;
    }

    /**
     * set a delete button to the form
     * @param string $name Button text
     * @param string $url URL of the query
     * @param string|number $dataID ID of the data to delete
     */
    public function setDeleteButton($name, $url, $dataID)
    {
        $this->deleteButtonName = $name;
        $this->deleteURL = $url;
        $this->deleteID = $dataID;
    }

    /**
     * Internal method to set an input value either using the default value, or the request value
     * @param string $name Value name
     * @param string $value Default value
     */
    protected function setValue($value)
    {
        $this->values[] = $value;
    }

    /**
     * Add a label of type h1 to partition the form
     * @param type $name Label of the separator
     */
    public function addSeparator($name)
    {
        $this->types[] = "separator";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue("");
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add a label of type h2 to partition the form
     * @param type $name Label of the separator
     */
    public function addSeparator2($name)
    {
        $this->types[] = "separator2";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue("");
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add a comment field
     * @param type $text Text
     */
    public function addComment($text)
    {
        $this->types[] = "comment";
        $this->names[] = $text;
        $this->labels[] = "";
        $this->setValue("");
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add hidden input to the form
     * @param string $name Input name
     * @param string $value Input default value
     */
    public function addHidden($name, $value = "")
    {
        $this->types[] = "hidden";
        $this->names[] = $name;
        $this->labels[] = "";
        $this->setValue($value);
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add an upload button to upload file
     * @param type $name
     * @param type $label
     */
    public function addUpload($name, $label, $value = "")
    {
        $this->types[] = "upload";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->useUpload = true;
        $this->enabled[] = "";
        $this->setValue($value);
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * To download a file from the database
     * @param string $name
     * @param string $label
     * @param string $url
     */
    public function addDownloadButton($name, $label, $url)
    {
        $this->types[] = "downloadbutton";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->isMandatory[] = false;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->setValue($url);
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add text input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    // #105: add readonly
    public function addText($name, $label, $isMandatory = false, $value = "", $enabled = "", $readonly = "", $checkUnicity = false, $suggestLogin = false)
    {
        $this->types[] = "text";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = $enabled;
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = $readonly;
        $this->checkUnicity[] = $checkUnicity;
        if ($suggestLogin) {
            $this->suggestLogin = true;
        }
    }

    /**
     * Password field
     * @param type $name Form variable name
     * @param type $label Field label
     * @param type $isMandatory is mandatory field
     */
    public function addPassword($name, $label, $isMandatory = true)
    {
        $this->types[] = "password";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue("");
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add date field
     * @param type $name Form variable name
     * @param type $label Field label
     * @param type $isMandatory is mandatory field
     * @param type $value default value
     */
    public function addDate($name, $label, $isMandatory = false, $value = "")
    {
        $this->types[] = "date";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    public function addDatetime($name, $label, $isMandatory = false, $value = array("", "", ""))
    {
        $this->types[] = "datetime";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    public function addHour($name, $label, $isMandatory = false, $value = array("", ""))
    {
        $this->types[] = "hour";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add color input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addColor($name, $label, $isMandatory = false, $value = "")
    {
        $this->types[] = "color";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add email input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addEmail($name, $label, $isMandatory = false, $value = "", $checkUnicity = false)
    {
        $this->types[] = "email";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = $checkUnicity;
    }

    /**
     * Add number input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addNumber($name, $label, $isMandatory = false, $value = "")
    {
        $this->types[] = "number";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add float input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addFloat($name, $label, $isMandatory = false, $value = "")
    {
        $this->types[] = "float";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add select input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param unknown $choices List of options names
     * @param unknown $choicesid List of options ids
     * @param string $value Input default value
     */
    public function addSelect($name, $label, $choices, $choicesid, $value = "", $submitOnChange = false)
    {
        $this->types[] = "select";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = false;
        $this->choices[] = $choices;
        $this->choicesid[] = $choicesid;
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = $submitOnChange;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

        /**
     * Add mandatory select input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param unknown $choices List of options names
     * @param unknown $choicesid List of options ids
     * @param string $value Input default value
     */
    public function addSelectMandatory($name, $label, $choices, $choicesid, $value = "", $submitOnChange = false)
    {
        $this->types[] = "select";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = true;
        $this->choices[] = $choices;
        $this->choicesid[] = $choicesid;
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->useJavascript[] = false;
        $this->submitOnChange[] = $submitOnChange;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }


    /**
     * Add textarea input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    public function addTextArea($name, $label, $isMandatory = false, $value = "", $userichtxt = false)
    {
        $this->types[] = "textarea";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue($value);
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->isTextArea = $userichtxt;
        $this->useJavascript[] = $userichtxt;
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add a combo list
     * @param type $label Field label
     * @param type $listNames List of choices name
     * @param type $listIds List of choices ids
     * @param type $values Default value
     */
    public function addChoicesList($label, $listNames, $listIds, $values)
    {
        $this->types[] = "choicesList";
        $this->names[] = "";
        $this->labels[] = $label;
        $this->isMandatory[] = false;
        $this->choices[] = $listNames;
        $this->choicesid[] = $listIds;
        $this->values[] = $values;
        $this->validated[] = true;
        $this->enabled[] = "";
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * Add a form add in the form
     * @param FormAdd $formAdd FotmAdd to add
     * @param type $label Label of the formAdd
     */
    public function setFormAdd(FormAdd $formAdd, $label = "")
    {
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
        $this->submitOnChange[] = false;
        $this->readonly[] = false;
        $this->checkUnicity[] = false;
    }

    /**
     * If formAdd, gets its Id
     * @return String
     */
    public function getFormAddId()
    {
        return $this->isFormAdd ? $this->formAdd->getId() : false;
    }

    /**
     * Internal function to add the form header
     * @return type HTML content
     */
    public function htmlOpen()
    {
        $formHtml = new FormHtml();
        return $formHtml->formHeader($this->validationURL, $this->id, $this->useUpload);
    }

    /**
     * Internal function to add the form footer
     * @return type
     */
    public function htmlClose()
    {
        $formHtml = new FormHtml();
        return $formHtml->formFooter();
    }

    /**
     * Generate the html code
     * @return string
     */
    public function getHtml($lang = "en", $headers = true)
    {
        $html = "";

        $formHtml = new FormHtml();

        $html .= $formHtml->title($this->title, $this->subtitle, $this->titlelevel);
        $html .= $formHtml->errorMessage($this->errorMessage);
        if ($headers) {
            $html .= $formHtml->formHeader($this->validationURL, $this->id, $this->useUpload);
        }
        $html .= $formHtml->id($this->id);

        if ($this->isTextArea === true) {
            $html .= $formHtml->textAreaScript();
        }

        // fields
        for ($i = 0; $i < count($this->types ?? []); $i++) {
            $readonlyElem = false;
            if ($this->readonly[$i]) {
                $readonlyElem = true;
            }

            $checkUnicityElem = false;
            if ($this->checkUnicity[$i]) {
                $checkUnicityElem = true;
            }

            $required = "";
            if ($this->isMandatory[$i]) {
                $required = "required";
            }
            $is_validated = "";
            if ($this->validated[$i] === false) {
                $is_validated = "alert alert-danger";
            }
            switch ($this->types[$i]) {
                case 'separator':
                    $html .= $formHtml->separator($this->names[$i], 3);
                    break;
                case 'separator2':
                    $html .= $formHtml->separator($this->names[$i], 5);
                    break;
                case 'comment':
                $html .= $formHtml->comment($this->names[$i], $this->labelWidth, $this->inputWidth);
                break;
                case 'hidden':
                    $html .= $formHtml->hidden($this->names[$i], $this->values[$i], $required);
                    break;
                case 'text':
                    $html .= $formHtml->text($is_validated, $this->labels[$i], $this->names[$i], $this->values[$i], $this->enabled[$i], $required, $this->labelWidth, $this->inputWidth, $readonlyElem, $checkUnicityElem);
                    break;
                case 'password':
                    $html .= $formHtml->password($is_validated, $this->labels[$i], $this->names[$i], $this->values[$i], $this->enabled[$i], $required, $this->labelWidth, $this->inputWidth);
                    break;
                case 'date':
                    $html .= $formHtml->date($is_validated, $this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
                    break;
                case 'datetime':
                    $html .= $formHtml->datetime($is_validated, $this->labels[$i], $this->names[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
                    break;
                case 'hour':
                    $html .= $formHtml->hour($is_validated, $this->labels[$i], $this->names[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
                    break;
                case 'color':
                    $html .= $formHtml->color($is_validated, $this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
                    break;
                case 'email':
                    $html .= $formHtml->email($is_validated, $this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth, $checkUnicityElem);
                    break;
                case 'number':
                    $html .= $formHtml->number($this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
                    break;
                case 'float':
                    $html .= $formHtml->number($this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth, true);
                    break;
                case 'textarea':
                    $html .= $formHtml->textarea($this->useJavascript[$i], $this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
                    break;
                case 'upload':
                    $html .= $formHtml->upload($this->labels[$i], $this->names[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
                    break;
                case 'downloadbutton':
                    $html .= $formHtml->downloadbutton($this->id, $this->labels[$i], $this->names[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
                    break;
                case 'select':
                    $sub = "";
                    if ($this->submitOnChange[$i]) {
                        $sub = $this->id;
                    }
                    $html .= $formHtml->select($this->labels[$i], $this->names[$i], $this->choices[$i], $this->choicesid[$i], $this->values[$i], $this->isMandatory[$i], $this->labelWidth, $this->inputWidth, $sub);
                    break;
                case 'formAdd':
                    $html .= $this->formAdd->getHtml($this->labels[$i], $this->labelWidth, $this->inputWidth);
                    break;
                case 'choicesList':
                    $html .= $formHtml->choicesList($this->labels[$i], $this->choices[$i], $this->choicesid[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
                    break;
                default:
                    break;
            }
        }

        // buttons area
        $html .= $formHtml->buttons($this->id, $this->validationButtonName, $this->cancelURL, $this->cancelButtonName, $this->deleteURL, $this->deleteID, $this->deleteButtonName, $this->externalButtons, $this->buttonsWidth, $this->buttonsOffset);

        if ($headers) {
            $html .= $formHtml->formFooter();
        }


        if ($this->checkUnicity && in_array(true, $this->checkUnicity)) {
            $html .= $formHtml->checkUnicityScript();
        }

        if ($this->suggestLogin) {
            $html .= $formHtml->suggestLoginScript();
        }

        if ($this->isFormAdd === true) {
            $html .= $this->formAdd->getJavascript();
        }

        return $html;
    }

    /**
     * Check if the form is valid
     * @return number
     */
    public function check()
    {
        $formID = $this->request->getParameterNoException("formid");
        if ($formID == $this->id) {
            Configuration::getLogger()->debug('[form=check] form submit', ['form' => $this->id, 'data' => $this->request->params()]);
            return 1;
        }
        return 0;
    }

    /**
     * Get input from query
     * @param unknown $name Input name
     * @return string Input value
     */
    public function getParameter($name)
    {
        return $this->request->getParameter($name);
    }
}
