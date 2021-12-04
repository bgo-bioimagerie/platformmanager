<?php

require_once 'Framework/Request.php';
require_once 'Framework/FormAdd.php';
require_once 'Framework/FormHtml.php';
require_once 'Modules/core/Model/CoreTranslator.php';

abstract class FormBaseElement {

    protected ?string $label = null;
    protected string $id = '';
    protected string $name = '';
    protected string|int $value;
    protected ?string $placeholder = null;
    protected bool $mandatory = false;
    protected bool $readOnly = false;
    // type of input see https://developer.mozilla.org/fr/docs/Web/HTML/Element/Input
    protected string $type="text";
    protected ?string $caption = null;
    protected ?string $autocomplete = null;
    protected ?string $error = null;
    protected array $classes = [];
    protected ?string $unique = null; // login, email
    protected ?string $equals = null; // unique identifier for fields to match
    protected ?array $suggests = null;  // array of element ids for a suggestion (['firstname', 'lastname'] for ex)

    protected array $javascript = [];

    abstract function html(?string $user=null, ?string $id_space=null): string;

    public function getName(): string {
        return $this->name;
    }

    public function getError(): ?string {
        return $this->error;
    }

    public function Validate(?Request $request): bool {
        if($request === null) {
            return false;
        }
        $this->setError(null);
        $p = $request->getParameterNoException($this->name);
        if($this->mandatory && ($p === null || $p === "")) {
                $this->setError('missing/empty parameter');
                return false;
        }
        return true;
    }

    protected function getValue(?Request $request): mixed {
        return $request !==null ? $request->getParameterNoException($this->name) : null;
    }

    public function Javascript(): array {
        return $this->javascript;
    }

    public function __construct(string $name, string|int $value='', bool $multiple=false, string $placeholder=null) {
        $this->id = $name;
        $this->name = $multiple ? $name."[]" : $name;
        $this->value = $value;
        $this->placeholder = $placeholder;
    }

    public function setLabel(string $label) : FormBaseElement {
        $this->label = $label;
        return $this;
    }

    /**
     * Add autocomplete elt
     * 
     * https://developer.mozilla.org/fr/docs/Web/HTML/Attributes/autocomplete
     */
    public function setAutocomplete(string $auto) : FormBaseElement {
        $this->autocomplete = $auto;
        return $this;
    }

    public function setError(?string $error) : FormBaseElement {
        $this->error = $error;
        return $this;
    }


    public function setCaption(string $caption) : FormBaseElement {
        $this->caption = $caption;
        return $this;
    }

    public function setType(string $type) : FormBaseElement {
        $this->type = $type;
        if($this->type == 'email') {
            $this->javascript['unique'] = "control.loadEmails();\n";
        }
        return $this;
    }

    public function setMandatory(bool $mandatory=true) : FormBaseElement {
        $this->mandatory = $mandatory;
        return $this;
    }

    public function setReadOnly(bool $readOnly=true) : FormBaseElement {
        $this->readOnly = $readOnly;
        return $this;
    }

    public function setUnique(string $unique='login') : FormBaseElement {
        $this->unique = $unique;
        $this->javascript['unique'] = "control.loadUniques();\n";
        return $this;
    }

    public function setEquals(string $equals=null) : FormBaseElement {
        $this->equals = $equals;
        $this->javascript['equals'] = "control.loadEquals();\n";
        return $this;
    }

    public function setSuggests(array $suggests=null) : FormBaseElement {
        $this->suggests = $suggests;
        $this->javascript['suggests'] = "control.loadSuggests();\n";
        return $this;
    }

    /**
     * Get common options
     */
    protected function options(?string $user=null, ?string $id_space=null): string {
        $options = '';
        if($this->mandatory) {
            $options .= ' required';
        }
        if($this->readOnly) {
            $options .= ' readonly';
        }
        if($this->autocomplete) {
            $options .= ' autocomplete="'.$this->autocomplete.'"';
        }
        if($this->unique) {
            $options .= ' x-unique="'.$this->unique.'"';
            if($user) {
                $options .= 'x-id="'.$user.'"';
            }
        }
        if($this->type == 'email') {
            $options .= 'x-email';
        }
        if($this->equals) {
            $options .= ' x-equal="'.$this->equals.'"';
        }
        if($this->suggests) {
            $options .= ' x-suggest="'.implode(',', $this->suggests).'"';
        }
        return trim($options);
    }

    /**
     * Generate HTML for form element
     */
    public function toHtml(?string $user=null, ?string $id_space=null) : string {
        $html = '  <div class="form-group row">'."\n";
        if ($this->label) {
            $html .= '    <div class="col-xs-12 col-md-2">'."\n";
            $extra = '';
            if($this->mandatory) { $extra = '*'; }
            $html .= '      <label class="form-label" for="'.$this->id.'">'.$this->label.$extra.'</label>'."\n";
            $html .= '    </div>'."\n";
        
            $html .= '    <div class="col-xs-12 col-md-10">'."\n";
        } else {
            $html .= '    <div class="col-xs-12">'."\n";
        }

        $html .= $this->html($user, $id_space);
        if ($this->caption) {
            $html .= '      <small class="form-text text-muted">'.$this->caption.'</small>'."\n";
        }
        if ($this->error) {
            $html .= '      <div class="alert alert-error" role="alert">'.$this->error.'</div>'."\n";
        }
        $html .= '    </div>'."\n";
        $html .= '  </div>'."\n";
        return $html;
    }

    public function getClasses() : string {
        return implode(" ", $this->classes);
    }

}

/**
 * Create an <input> html element
 * 
 * $e = new FormInputElement("myinput", "hello");
 * $e->label("gimme ur name")->setMandatory()
 */
class FormInputElement extends FormBaseElement {

    public function __construct($name, $value='', $multiple=false, $placeholder=null) {
        parent::__construct($name, $value, $multiple, $placeholder);
        $this->setType('text');
    }

    function html(?string $user=null, ?string $id_space=null) : string {
        return '    <input '.$this->options($user, $id_space).' type="'.$this->type.'" class="form-control '.$this->getClasses().'" id="'.$this->id.'" name="'.$this->name.'" placeholder="'.$this->placeholder.'" value="'.$this->value.'"/>'."\n";
    }
}

class FormTextElement extends FormBaseElement {
    function html(?string $user=null, ?string $id_space=null) : string {
        return '    <textarea '.$this->options($user, $id_space).'" class="form-control '.$this->getClasses().'" id="'.$this->id.'" name="'.$this->name.'" placeholder="'.$this->placeholder.'">'.$this->value.'</textarea>'."\n";
    }
}


class FormRadioElement extends FormCheckboxElement {
    public function __construct($name, $value='', $multiple=false, $placeholder=null) {
        parent::__construct($name, $value, $multiple, $placeholder);
        $this->setType('radio');
    }
}

class FormRadiosElement extends FormCheckboxesElement {
    public function __construct($name, $value='', $multiple=false, $placeholder=null) {
        parent::__construct($name, $value, $multiple, $placeholder);
        $this->setType('radio');
    }
}

class FormCheckboxElement extends FormBaseElement {

    public function __construct($name, $value='', $multiple=false, $placeholder=null) {
        parent::__construct($name, $value, $multiple, $placeholder);
        $this->setType('checkbox');
    }

    function html(?string $user=null, ?string $id_space=null) : string {
        $checked = "";
        if($this->value == 1 || $this->value === true) {
            $checked = "checked";
        }
        return '  <input type="'.$this->type.'"'.$this->options($user, $id_space).' class="form-check-input '.$this->getClasses().'" id="'.$this->id.'" name="'.$this->name.'" '.$checked.' >'."\n";

    }

}

class FormCheckboxesElement extends FormBaseElement {
    private $boxes = [];

    public function __construct($name, $value='', $multiple=false, $placeholder=null) {
        parent::__construct($name, $value, $multiple, $placeholder);
        $this->setType('checkbox');
    }

    /**
     * Add a checkbox
     * 
     * @var []FormCheckboxElement $box
     */
    public function add($box) : FormCheckboxesElement{
        if (is_array($box)) {
            $this->boxes = array_merge($this->boxes, $box);
        } else {
        $this->boxes[] = $box;
        }
        return $this;
    }

    function html(?string $user=null, ?string $id_space=null) : string {
        $html = '    <div class="checkbox">'."\n";
        foreach ($this->boxes as $box) {
            $html .= '      <div class="row">'."\n";
            $html .= '        <div class="col-xs-12">'."\n";
            $html .= '          <div class="form-check">'."\n";
            $html .= '          '.$box->html($user, $id_space);
            $html .= '            <label class="form-check-label" for="'.$box->id.'">'.$box->name.'</label>'."\n";
            $html .= '          </div>'."\n";
            $html .= '        </div>'."\n";
            $html .= "      </div>\n";
        }
        $html .= '    </div>'."\n";



        return $html;
    }
}

class FormSeparatorElement extends FormBaseElement {

    private int $level = 3;

    public function __construct($name, $value='', $level=3) {
        parent::__construct($name, $value);
        $this->level = $level;
    }

    function html(?string $user=null, ?string $id_space=null): string {
        if(!$this->name) {
            return '<hr/>'."\n";
        }
        $html = '<div class="row">'."\n";
        $html .= '  <div class="col-xs-12">'."\n";
        $html .= "    <h".$this->level.">".$this->name."</h".$this->level.">"."\n";
        $html .= "  </div>\n";
        $html .= '</div>'."\n";
        return $html;
    }

}

class FormComment extends FormBaseElement {

    function html(?string $user=null, ?string $id_space=null): string {
        $html = '<div class="row">'."\n";
        $html .= '  <div class="col-xs-12">'."\n";
        $html .= "    <!-- ".$this->value." -->\n";
        $html .= "    <p>".$this->name."</p>"."\n";
        $html .= "  </div>\n";
        $html .= '</div>'."\n";
        return $html;
    }
}


class FormOptionElement extends FormBaseElement {

    function html(?string $user=null, ?string $id_space=null): string {
        return '<option value="'.$this->value.'">'.$this->name.'</option>'."\n";
    }
}

class FormUploadElement extends FormInputElement {

    public function __construct($name, $value='') {
        parent::__construct($name, $value);
        $this->type = 'file';
    }

}

class FormIntegerElement extends FormFloatlement {

    public function __construct(string $name, int $value=0) {
        parent::__construct($name, $value);
        $this->type = 'number';
        $this->step = '1';
    }

    protected function getValue(?Request $request): mixed {
        if($request !== null) {
            $val = $request->getParameterNoException($this->name);
            return intval($val);
        }
        return null;
    }

}

class FormFloatlement extends FormInputElement {

    protected string $step = "any";
    protected mixed $min = null;
    protected mixed $max = null;

    public function __construct(string $name, float $value=0) {
        parent::__construct($name, $value);
        $this->type = 'number';
    }

    protected function getValue(?Request $request): mixed {
        if($request !== null) {
            $val = $request->getParameterNoException($this->name);
            return floatval($val);
        }
        return null;
    }

    protected function baseValidation($request): bool {
        return parent::Validate($request);   
    }

    protected function isInRange(mixed $value): bool {
        if($this->min !== null && $value < $this->min) {
            return false;
        }
        if($this->max !== null && $value > $this->max) {
            return false;
        }
        return true;
    }

    public function Validate(?Request $request): bool {
        if(! $this->baseValidation($request)) {
            return false;
        }
        
        try {
            $val = $this->getValue($request);
            if(!$this->isInRange($val)) {
                $this->setError(sprintf('value not in range [%s,%s]', $this->min, $this->max));
                return false;
            }
        } catch(Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @var mixed $min int or float min value
     * @var mixed $max int or float max value
     * @var string $step optional step value, defaults to any for float, 1 for integers
     */
    public function setRange(mixed $min=null, mixed $max=null, ?string $step=null):FormBaseElement {
        if($min !== null) {
            $this->min = $min;
            if($this->value < $min) {
                $this->value = $min;
            }
        }
        if($max !== null) {
            $this->max = $max;
        }
        if($step !== null) {
            $this->step = $step;
        }
        return $this;
    }

    function html(?string $user=null, ?string $id_space=null) : string {
        $minCondition = '';
        $maxCondition = '';
        if($this->min !== null) {
            $minCondition = sprintf('min="%s"', $this->min);

        }
        if($this->max !== null) {
            $maxCondition = sprintf('max="%s"', $this->max);

        }
        return '    <input '.$minCondition.' '.$maxCondition.' step="'.$this->step.'" '.$this->options($user, $id_space).' type="'.$this->type.'" class="form-control '.$this->getClasses().'" id="'.$this->id.'" name="'.$this->name.'" placeholder="'.$this->placeholder.'" value="'.$this->value.'"/>'."\n";
    }

}

class FormHiddenElement extends FormInputElement {

    public function __construct($name, $value='') {
        parent::__construct($name, $value);
        $this->type = 'hidden';
    }

}

class FormPasswordElement extends FormInputElement {

    public function __construct($name, $value='') {
        parent::__construct($name, $value);
        $this->type = 'password';
    }

}

class FormEmailElement extends FormInputElement {

    public function __construct($name, $value='') {
        parent::__construct($name, $value);
        $this->type = 'email';
    }

}

class FormColorElement extends FormInputElement {

    public function __construct($name, $value='#000000') {
        parent::__construct($name, $value);
        $this->type = 'color';
    }

}

class FormDownloadElement extends FormBaseElement {

    private string $dclass = 'primary';

    public function __construct($name, $value, $dclass='primary') {
        parent::__construct($name, $value);
        $this->dclass = $dclass;
    }


    public function html(?string $user=null, ?string $id_space=null): string {
        return '      <a target="_blank" rel="noreferrer,noopener" href="'.$this->value.'"><button type="button" class="btn btn-'.$this->dclass.'">'.$this->name.'</button></a>'."\n";
    }
}

class FormSelectElement extends FormBaseElement {

    // FormOptionElement[]
    private $options = [];

    /**
     * Set options
     * 
     * @var FormOptionElement[] $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /*
    * Add option
    * 
    * @var FormOptionElement $option
    */
   public function setOption($option) {
       $this->options[] = $option;
   }

   public function html(?string $user=null, ?string $id_space=null): string {
       $html = '<select class="form-control '.$this->getClasses().'" '.$this->options($user, $id_space).' id="'.$this->name.'" name="'.$this->name.'" value="'.$this->value.'">'."\n";
       foreach($this->options as $option) {
           $html .= $option->html()."\n";
       }
       $html .= '</select>'."\n";
       return $html;
   }


}

/**
 * TODO 
 * FormFileUpload, -> type file
 * FormFileDownload,
 * FormCheckBoxes,
 * FormDate  -> type date
 * FormPassword  -> type password
 * FormText (textarea)
 * FormComment // just some text
 * addExternalButton
 * setTitle
 * setSubTitle
 * deletebutton
 * addseparator && addseparator2
 * addcomment
 * adddownloadbutton
 * addText -> forminput + readonly
 * addPassword -> forminput + type password
 * addDate -> forminput + type date
 * addDateTime -> forminput + type datetime-local
 * addHour -> forminput + type time with opt min and max  min="09:00" max="18:00"
 * addColor -> forminput + type color
 * addEmail -> forminput + type email
 * addNumber -> forminput + type number with opt min max
 * addSelect -> formselect
 * addTextArea -> formText
 * addChoicesList
 * setFormAdd
 */

/**
 * Generate an HTML form
 */
class PfmForm {

    private ?Request $request = null;
    static ?string $user = null;
    static ?int $id_space = null;

    public ?string $title = null;
    public ?string $subtitle = null;

    // Name of form
    private string $name;
    // URL to post form
    private string $url;
    // @var FormBaseElement[]
    private array $elts = [];

    private ?string $cancelUrl = null;
    private ?string $deleteUrl = null;
    private array $buttons = [];

    public function setRequest(Request $request) {
        $this->request = $request;
    }

    public function Javascript():string {
        $html =  "\n<script type=\"module\">\n";
        $html .= "import {FormControls} from '/externals/pfm/controls/formcontrols_script.js';\n";
        $html .= 'document.addEventListener("DOMContentLoaded", function(event) {'."\n";
        $html .= "  let control = new FormControls();\n";
        $html .= "  control.loadForms();\n";

        $js = [];
        foreach ($this->elts as $elt) {
            foreach($elt->Javascript() as $key => $value) {
                if(isset($js[$key])) {
                    continue;
                }
                $html .= "  // $key\n";
                $html .= "  $value\n";
                $js[$key] = $value;
            }
        }

        $html .= "});\n";
        $html .= "</script>\n";
        return $html;
    }

    public function setUser(string $user) {
        self::$user = $user;
        return $this;
    }

    public function setTitle(string $title, string $subtitle='') {
        $this->title = $title;
        $this->subtitle = $subtitle;
        return $this;
    }

    public function setSpace(string $space) {
        self::$id_space = $space;
        return $this;
    }


    public function __construct(string $name, string $url=null, Request $request=null) {
        $this->name = $name;
        $this->url = $url;
        $this->request = $request;
    }

    public function add(FormBaseElement $elt) {
        $this->elts[] = $elt;
        return $this;
    }

    public function addCancel(string $url) {
        $this->cancelUrl = $url;
        return $this;
    }

    public function addDelete(string $url, string $id=null) {
        $this->deleteUrl = $url;
        if($id) {
            $this->deleteUrl .= "/$id";
        }
        return $this;
    }

    public function setUrl(string $url) {
        $this->url = $url;
        return $this;
    }

    public function addButton(string $name, $url, $class='danger', $newWindow=false){
        $this->buttons[] = ['name' => $name, 'url' => $url, 'new' => $newWindow, 'class' => $class?$class:'primary'];
    }

    public function isSubmitted(): bool {
        if(!$this->request) {
            return false;
        }
        return $this->request->getParameterNoException('form_id') == $this->name ? true : false;
    }

    /**
     * Check form input are valid and set optional input object public properties from values
     */
    public function Validate(?object $object=null): bool {
        if(!$this->isSubmitted()) {
            return false;
        }
        $isValid = true;
        // $objClass = $object !== null ? get_class($object) : null;
        foreach ($this->elts as $elt) {
            if(!$elt->Validate($this->request)) {
                $isValid = false;
                break;
            }
            if ($object !== null && property_exists($object, $elt->getName())){
                $val = $elt->getValue();
                if($val !== null) {
                    $object->{$elt->name} = $val;
                }
            }
        }
        return $isValid;
    }

    public function Errors(): array {
        $errors = [];
        foreach ($this->elts as $elt) {
            $err = $elt->getError();
            if($err) {
                $errors[] = ['name' => $elt->getName(), 'error' => $err];
            }
        }
        return $errors;
    }

    /**
     * Generate HTML for form element
     */
    public function toHtml($lang='en'): string {
        $html = '';
        if($this->title) {
            $html .= '<div class="row>'."\n";
            $html .= '  <div class="col-xs-12">'."\n";
            $html .= '<h3>'.$this->title.'</h3>';
            if($this->subtitle) {
                $html.= '<p><small>'.$this->subtitle.'</small></p>'."\n";
            }
            $html .= '</div>'."\n";
            $html .= '</div>'."\n";
            
        }
        $action= $this->url ? 'action="'.$this->url.'"' : '';
        $html .= '<form x-form class="form" id="'.$this->name.'" method="post" enctype="multipart/form-data" '.$action.'>'."\n";
        $html .= (new FormHiddenElement('form_id', $this->name))->toHtml()."\n";
        foreach ($this->elts as $elt) {
            $html .= $elt->toHtml(self::$user, self::$id_space)."\n";
        }
        $html .= '  <div class="row">'."\n";
        $html .= '    <div class="col-xs-12 col-md-4">'."\n";
        $html .= '      <button type="submit" class="btn btn-primary">'.CoreTranslator::Save($lang).'</button>'."\n";
        $html .= '    </div>'."\n";
        if($this->cancelUrl) {
            $html .= '    <div class="col-xs-12 col-md-4">'."\n";
            $html .= '      <a href="'.$this->cancelUrl.'"><button type="button" class="btn btn-primary">'.CoreTranslator::Cancel($lang).'</button></a>'."\n";
            $html .= '    </div>'."\n";

        }
        if($this->deleteUrl) {
            $html .= '    <div class="col-xs-12 col-md-4">'."\n";
            $html .= '      <a href="'.$this->deleteUrl.'"><button type="button" class="btn btn-primary">'.CoreTranslator::Delete($lang).'</button></a>'."\n";
            $html .= '    </div>'."\n";
        }
        foreach ($this->buttons as $button) {
            if($button['new']) {
                $html .= '    <div class="col-xs-12 col-md-4">'."\n";
                $html .= '      <a target="_blank" rel="noreferrer,noopener" href="'.$button['url'].'"><button type="button" class="btn btn-'.$button['class'].'">'.$button['name'].'</button></a>'."\n";
                $html .= '    </div>'."\n";
            }
        }
        $html .= '  </div>'."\n";
        $html .= '</form>'."\n";

        return $html;
    }


}


/**
 * Class allowing to generate and check a form html view. 
 * 
 * @author Sylvain Prigent
 */
class Form {

    // var @FormBaseElements[]
    private PfmForm $pfmform;

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
    private $externalButtons;

    /**
     * Constructor
     * @param Request $request Request that contains the post data
     * @param string $id Form ID
     */
    public function __construct(Request $request, $id, $useAjax = false) {
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
        $this->isDate = false;

        $this->pfmform = new PfmForm($id);
    }

    /**
     * 
     * @param type $buttonsWidth Number of bootstrap columns for the buttons
     * @param type $buttonsOffset Number of offset bootstrap columns
     */
    public function setButtonsWidth($buttonsWidth, $buttonsOffset) {
        //$this->buttonsWidth = $buttonsWidth;
        //$this->buttonsOffset = $buttonsOffset;
    }

    /**
     * 
     * @param type $labelWidth Number of bootstrap columns for the form labels
     * @param type $inputWidth Number of bootstrap columns for the form fields
     */
    public function setColumnsWidth($labelWidth, $inputWidth) {
        $this->labelWidth = $labelWidth;
        $this->inputWidth = $inputWidth;
    }

    /**
     * Add a button in the form validation button bar
     * @param string $name Button text
     * @param string $url Action URL
     * @param string $type Bootstrap button type
     * @param bool $newtab , on click open a new window
     */
    public function addExternalButton($name, $url, $type = "danger", $newtab = false) {
        $this->externalButtons[] = array("name" => $name, "url" => $url, "type" => $type, "newtab" => $newtab);
        $this->pfmform->addButton($name, $url, $type, $newtab);
        
    }

    /**
     * Set the form title
     * @param string $title Form title
     */
    public function setTitle($title, $level = 3) {
        $this->title = $title;
        $this->titlelevel = $level;
        $this->pfmform->title = $title;
    }

    /**
     * Set the form sub title
     * @param string $subtitle Form sub title
     */
    public function setSubTitle($subtitle) {
        $this->subtitle = $subtitle;
        $this->pfmform->subtitle =  $subtitle;
    }

    /**
     * Set a validation button to the title
     * @param string $name Button text
     * @param string $url URL of the form post query
     */
    public function setValidationButton($name, $url) {
        $this->validationButtonName = $name;
        $this->validationURL = $url;
        $this->pfmform->setUrl($url);
    }

    /**
     * 
     * @param string $url URL of the validation button
     */
    public function setValisationUrl($url) {
        $this->validationURL = $url;
        $this->pfmform->setUrl($url);
    }

    /**
     * Set a cancel button to the form
     * @param string $name Button text
     * @param string $url URL redirection
     */
    public function setCancelButton($name, $url) {
        $this->cancelButtonName = $name;
        $this->cancelURL = $url;
        $this->pfmform->addCancel($url);
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
        $this->pfmform->addDelete($url, $dataID);
    }

    /**
     * Internal method to set an input value either using the default value, or the request value
     * @param string $name Value name
     * @param string $value Default value
     */
    protected function setValue($value) {
        //if ($this->parseRequest) {
        //    $this->values[] = $this->request->getParameterNoException($name);
        //} else {
        $this->values[] = $value;
        //}
    }

    /**
     * Add a label of type h1 to partition the form
     * @param type $name Label of the separator
     */
    public function addSeparator($name) {
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
        $this->pfmform->add(new FormSeparatorElement($name));
    }

    /**
     * Add a label of type h2 to partition the form
     * @param type $name Label of the separator
     */
    public function addSeparator2($name) {
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
        $this->pfmform->add(new FormSeparatorElement($name));
    }

    /**
     * Add a comment field
     * @param string $text Text
     */
    public function addComment($text) {
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
        $this->pfmform->add(new FormComment($text));
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
        $f = new FormInputElement($name, $value);
        $this->pfmform->add($f->setType("hidden"));
    }

    /**
     * Add an upload button to upload file
     * @param string $name 
     * @param string $label
     */
    public function addUpload($name, $label, $value = "") {
       
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

        $f = new FormUploadElement($name, $value);
        $this->pfmform->add($f->setLabel($label));
    }

    /**
     * To download a file from the database
     * @param string $name
     * @param string $label
     * @param string $url
     */
    public function addDownloadButton($name, $label, $url) {
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
        $f = new FormDownloadElement($name, $url);
        $this->pfmform->add($f->setLabel($label));

    }

    /**
     * Add text input to the form
     * @param string $name Input name
     * @param string $label Input label 
     * @param string $isMandatory True if mandatory input
     * @param string $value Input default value
     */
    // #105: add readonly
    public function addText($name, $label, $isMandatory = false, $value = "", $enabled = "", $readonly = "", $checkUnicity = false, $suggestLogin = false) {
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
        $f = new FormInputElement($name, $value);
        if($readonly || !$enabled) {  $f->setReadOnly(); }
        if($checkUnicity) {  $f->setUnique($name); }
        if($isMandatory) {$f->setMandatory(); }
        $this->pfmform->add($f);
    }

    /**
     * Password field
     * @param type $name Form variable name
     * @param type $label Field label
     * @param type $isMandatory is mandatory field
     */
    public function addPassword($name, $label, $isMandatory = true) {
        $this->types[] = "password";
        $this->names[] = $name;
        $this->labels[] = $label;
        $this->setValue("");
        $this->isMandatory[] = $isMandatory;
        $this->choices[] = array();
        $this->choicesid[] = array();
        $this->validated[] = true;
        $this->enabled[] = true;
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
    public function addDate($name, $label, $isMandatory = false, $value = "") {
        $this->isDate = true;
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

    public function addDatetime($name, $label, $isMandatory = false, $value = array("", "", "")) {
        $this->isDate = true;
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

    public function addHour($name, $label, $isMandatory = false, $value = array("", "")) {
        $this->isDate = true;
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
    public function addColor($name, $label, $isMandatory = false, $value = "") {
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
    public function addEmail($name, $label, $isMandatory = false, $value = "", $checkUnicity = false) {
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
    public function addNumber($name, $label, $isMandatory = false, $value = "") {
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
     * Add select input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param unknown $choices List of options names
     * @param unknown $choicesid List of options ids
     * @param string $value Input default value
     */
    public function addSelect($name, $label, $choices, $choicesid, $value = "", $submitOnChange = false) {
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
     * Add select input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param unknown $choices List of options names
     * @param unknown $choicesid List of options ids
     * @param string $value Input default value
     */
    public function addSelectMandatory($name, $label, $choices, $choicesid, $value = "", $submitOnChange = false) {
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
    public function addTextArea($name, $label, $isMandatory = false, $value = "", $userichtxt = false) {
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
    public function addChoicesList($label, $listNames, $listIds, $values) {
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
    public function setFormAdd(FormAdd $formAdd, $label = "") {
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
     * Internal function to add the form header
     * @return type HTML content
     */
    public function htmlOpen() {
        $formHtml = new FormHtml();
        return $formHtml->formHeader($this->validationURL, $this->id, $this->useUpload);
    }

    /**
     * Internal function to add the form footer 
     * @return type
     */
    public function htmlClose() {
        $formHtml = new FormHtml();
        return $formHtml->formFooter();
    }

    /**
     * Generate the html code
     * @return string
     */
    public function getHtml($lang = "en", $headers = true) {


        $html = "";

        $formHtml = new FormHtml();

        $html .= $formHtml->title($this->title, $this->subtitle, $this->titlelevel);
        $html .= $formHtml->errorMessage($this->errorMessage);
        if ($headers) {
            $html .= $formHtml->formHeader($this->validationURL, $this->id, $this->useUpload);
        }
        $html .= $formHtml->id($this->id);

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
            $validated = "";
            if ($this->validated[$i] === false) {
                $validated = "alert alert-danger";
            }
            if ($this->types[$i] == "separator") {
                $html .= $formHtml->separator($this->names[$i], 3);
            }
            if ($this->types[$i] == "separator2") {
                $html .= $formHtml->separator($this->names[$i], 5);
            }
            if ($this->types[$i] == "comment") {
                $html .= $formHtml->comment($this->names[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "hidden") {
                $html .= $formHtml->hidden($this->names[$i], $this->values[$i], $required);
            }
            if ($this->types[$i] == "text") {
                $html .= $formHtml->text($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $this->enabled[$i], $required, $this->labelWidth, $this->inputWidth, $readonlyElem, $checkUnicityElem);
            }
            if ($this->types[$i] == "password") {
                $html .= $formHtml->password($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $this->enabled[$i], $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "date") {
                $html .= $formHtml->date($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $lang, $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "datetime") {
                $html .= $formHtml->datetime($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $lang, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "hour") {
                $html .= $formHtml->hour($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $lang, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "color") {
                $html .= $formHtml->color($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "email") {
                $html .= $formHtml->email($validated, $this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth, $checkUnicityElem);
            }
            if ($this->types[$i] == "number") {
                $html .= $formHtml->number($this->labels[$i], $this->names[$i], $this->values[$i], $required, $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "textarea") {
                $html .= $formHtml->textarea($this->useJavascript[$i], $this->labels[$i], $this->names[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "upload") {
                $html .= $formHtml->upload($this->labels[$i], $this->names[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "downloadbutton") {
                $html .= $formHtml->downloadbutton($this->id, $this->labels[$i], $this->names[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "select") {
                $sub = "";
                if ($this->submitOnChange[$i]) {
                    $sub = $this->id;
                }
                $html .= $formHtml->select($this->labels[$i], $this->names[$i], $this->choices[$i], $this->choicesid[$i], $this->values[$i], $this->isMandatory[$i], $this->labelWidth, $this->inputWidth, $sub);
            }
            if ($this->types[$i] == "formAdd") {
                $html .= $this->formAdd->getHtml($lang, $this->labels[$i], $this->labelWidth, $this->inputWidth);
            }
            if ($this->types[$i] == "choicesList") {
                $html .= $formHtml->choicesList($this->labels[$i], $this->choices[$i], $this->choicesid[$i], $this->values[$i], $this->labelWidth, $this->inputWidth);
            }
        }

        // buttons area
        $html .= $formHtml->buttons($this->id, $this->validationButtonName, $this->cancelURL, $this->cancelButtonName, $this->deleteURL, $this->deleteID, $this->deleteButtonName, $this->externalButtons, $this->buttonsWidth, $this->buttonsOffset);

        if ($headers) {
            $html .= $formHtml->formFooter();
        }

        if ($this->isDate === true) {
            $html .= $formHtml->timePickerScript();
        }
        if ($this->isTextArea === true) {
            $html .= $formHtml->textAreaScript();
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
        if ($this->useAjax) {
            //$html .= $formHtml->ajaxScript($this->id, $this->validationURL);
        }

        return $html;
    }

    /**
     * Check if the form is valid
     * @return number
     */
    public function check() {
        $formID = $this->request->getParameterNoException("formid");
        if ($formID == $this->id) {
            return 1;
        }
        Configuration::getLogger()->debug('[form=check] failed', ['form' => $this->id, 'data' => $this->request->params()]);
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
