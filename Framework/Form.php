<?php
/**
* Examples
* 
* require_once 'Framework/Form.php';
* $form = new PfmForm('hello', '/coretiles');
* $input1 = new FormInputElement('name','test');
* $form->add($input1->setLabel('Nom'));
* $select1 = new FormSelectElement('choice1', '2');
* $opt1 = new FormOptionElement('opt1', '1');
* $opt2 = new FormOptionElement('opt2', '2');
* $select1->add([$opt1, $opt2]);
* $form->add($select1->setLabel('Choices')->setEditable());
* echo $form->toHtml();
* echo $form->Javascript();
 */

require_once 'Framework/Request.php';
require_once 'Framework/FormAdd.php';
require_once 'Framework/FormHtml.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CoreSpace.php';

/**
 * Base class for form components
 */
abstract class FormBaseElement {

    protected ?string $label = null;
    protected string $id = '';
    protected string $name = '';
    protected string|int $value;
    protected ?string $placeholder = null;
    protected bool $mandatory = false;
    protected bool $readOnly = false;
    protected bool $nullable = false;
    // type of input see https://developer.mozilla.org/fr/docs/Web/HTML/Element/Input
    protected string $type="text";
    protected ?string $caption = null;
    protected ?string $autocomplete = null;
    protected ?string $error = null;
    protected array $classes = [];
    protected ?string $unique = null; // login, email
    protected ?string $equals = null; // unique identifier for fields to match
    protected ?array $suggests = null;  // array of element ids for a suggestion (['firstname', 'lastname'] for ex)

    protected ?int $role = null; //minimal required user role for validation

    protected bool $editable = false;

    protected array $javascript = [];

    public function __construct(string $name, mixed $value='', bool $multiple=false, string $placeholder=null) {
        $this->id = $name;
        $this->name = $multiple ? $name."[]" : $name;
        $this->value = $value === null ?  '' : $value;
        $this->placeholder = $placeholder;
    }

    /**
     * Generate html for element
     */
    abstract function html(?string $user=null, ?string $id_space=null): string;

    /**
     * USer can add/remove multiple elements
     */
    public function setEditable(bool $editable=true) {
        $this->name = $this->name."[]";
        $this->editable = $editable;
        $this->javascript['edit'] = "control.loadEditables();\n";
        return $this;
    }

    /**
     * Minimum validation role
    */
    public function Requires(int $role) {
        $this->role = $role;
    }

    /**
     * Get required role for element
     */
    public function Required(): ?int {
        return $this->role;
    }

    /**
     * Get element name
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Get element error
     */
    public function getError(): ?string {
        return $this->error;
    }

    /**
     * Validate element against request parameters and element constraints
     */
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
        if(!$this->nullable && $p === null) {
            $this->setError("parameter cannot be null");
            return false;
        }
        return true;
    }

    /**
     * Get element value from request parameters
     */
    public function getFormValue(?Request $request): mixed {
        return $request !==null ? $request->getParameterNoException($this->name) : null;
    }

    /**
     * Set element value
     */
    public function setValue(mixed $value) {
        $this->value = $value;
    }

    /**
     * Get element value
     */
    public function getValue(): mixed {
        return $this->value;
    }

    /**
     * Get javascript to insert in html for element
     */
    public function Javascript(): array {
        return $this->javascript;
    }

    /**
     * Set element label
     */
    public function setLabel(string $label) : FormBaseElement {
        $this->label = $label;
        return $this;
    }

    /**
     * Allow null values
     */
    public function setNullable(bool $allow=true): FormBaseElement {
        $this->nullable = $allow;
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

    /**
     * Set or unset element error
     */
    public function setError(?string $error) : FormBaseElement {
        $this->error = $error;
        return $this;
    }


    /**
     * Set element caption
     */
    public function setCaption(string $caption) : FormBaseElement {
        $this->caption = $caption;
        return $this;
    }

    /**
     * Set element type
     */
    public function setType(string $type) : FormBaseElement {
        $this->type = $type;
        if($this->type == 'email') {
            $this->javascript['unique'] = "control.loadEmails();\n";
        }
        return $this;
    }

    /**
     * Set/unset element as mandatory
     */
    public function setMandatory(bool $mandatory=true) : FormBaseElement {
        $this->mandatory = $mandatory;
        return $this;
    }

    /**
     * Set/unset element as readonly
     */
    public function setReadOnly(bool $readOnly=true) : FormBaseElement {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * Check that element is unique using controls library
     * 
     * @var string $unique type of control (login, email)
     */
    public function setUnique(string $unique='login') : FormBaseElement {
        $this->unique = $unique;
        $this->javascript['unique'] = "control.loadUniques();\n";
        return $this;
    }

    /**
     * Check that elements are equal using controls library
     * @var string equals  tag to use among elements to be equal
     */
    public function setEquals(string $equals=null) : FormBaseElement {
        $this->equals = $equals;
        $this->javascript['equals'] = "control.loadEquals();\n";
        return $this;
    }

    /**
     * Autofill element using controls library suggestion
     * @var array suggest list of element (2 only) ids to use ['firstname', 'lastname'] for example
     */
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
        if($this->editable) {
            $options .= ' x-edit';
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

    /**
     * get string with element CSS classes
     */
    public function getClasses() : string {
        return implode(" ", $this->classes);
    }

    /**
     * Set element CSS classes
     * @var array $classes list of string css classes to use
     */
    public function setClasses(array $classes) {
        $this->classes = $classes;
    }



}

/**
 * Create an <input> html element
 * 
 * $e = new FormInputElement("myinput", "hello");
 * $e->label("gimme ur name")->setMandatory()
 */
abstract class FormBaseInputElement extends FormBaseElement {

    public function __construct($name, $value='', $multiple=false, $placeholder=null) {
        parent::__construct($name, $value, $multiple, $placeholder);
        $this->setType('text');
    }

    function html(?string $user=null, ?string $id_space=null) : string {
        return '    <input '.$this->options($user, $id_space).' type="'.$this->type.'" class="form-control '.$this->getClasses().'" id="'.$this->id.'" name="'.$this->name.'" placeholder="'.$this->placeholder.'" value="'.$this->value.'"/>'."\n";
    }
}

/**
 * Type-ahead input element, allow user to put text
 * and display as dropdown the available options
 */
class FormTypeAheadElement extends FormInputElement {
    protected mixed $datalist = null;
    protected ?string $hiddenName = null;
    private bool $freetext = false;

    /**
     * Create a type ahead element
     * 
     * @var $name name of element
     * @var $value default value
     * @var $freetext allow user to specify a value not in options, default false
     * @var $placeholder placeholder text
     */
    public function __construct(string $name, mixed $value='', bool $freetext=false, string $placeholder=null) {
        parent::__construct($name, $value, false, $placeholder);
        $this->setType('text');
        $this->freetext = $freetext;
    }

    /**
     * Propose a list, user can type ahead values and it gets completed
     * @var $name list name
     * @var $options array of options [[name => text1, value => val1], [name => text2, value => val2]]
     */
    public function TypeAhead(string $name, array $options) {
        $this->datalist = ['name' => $name, 'options' => $options];
        $this->javascript['typeahead'] = "control.loadTypeAhead();\n";

        return $this;
    }

    function html(?string $user=null, ?string $id_space=null) : string {
        $this->hiddenName = $this->name;
        $this->name = "ta-" . $this->datalist['name'];
        $this->id = $this->name;
        $html = parent::html($user, $id_space);
        $hiddenElement = new FormHiddenElement($this->hiddenName, $this->value);
        $html .= $hiddenElement->html($user, $id_space);
        $html .= '<datalist id="'.$this->datalist['name'].'">'."\n";
        foreach ($this->datalist['options'] as $option) {
            $html .= sprintf('<option x-value=%s value="%s"/>', $option['value'], $option['name'])."\n";
        }
        $html .= '</datalist>'."\n";
        
        return $html;
    }

    protected function options(?string $user=null, ?string $id_space=null): string {
        $options = parent::options($user, $id_space);
        $options .= sprintf(' x-typeahead="%s" ', $this->hiddenName);
        if(!$this->freetext) {
            $options .= ' x-typelistonly';
        }
        return $options;
    }

    public function Validate(?Request $request): bool {
        if(! parent::Validate($request)) {
            return false;
        }
        if($this->freetext) {
            return true;
        }
        
        try {
            $val = $this->getFormValue($request);
            foreach($this->datalist as $dl) {
                if ($dl['value'] == $val) {
                    return true;
                }
            } 
        } catch(Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
        return false;
    }

}

class FormInputElement extends FormBaseInputElement {

    protected ?int $min = null;
    protected ?int $max = null;

    public function __construct($name, $value='', $multiple=false, $placeholder=null) {
        parent::__construct($name, $value, $multiple, $placeholder);
        $this->setType('text');
    }

    function html(?string $user=null, ?string $id_space=null) : string {
        return parent::html($user, $id_space);
    }

    /**
     * Sets min/max length of input
     * @var mixed $min int or float min value
     * @var mixed $max int or float max value
     */
    public function setRange(mixed $min=null, mixed $max=null):FormBaseElement {
        if($min !== null && $this->value < $min) {
            $this->value = $min;
        }
        if($max !== null && $this->value > $max) {
            $this->value = $max;
        }
        return $this;
    }

    /**
     * Get common options
     */
    protected function options(?string $user=null, ?string $id_space=null): string {
        $options = parent::options($user, $id_space);

        if($this->min !== null) {
            $options .= sprintf(' minlength="%s" ', $this->min);

        }
        if($this->max !== null) {
            $options .= sprintf(' maxlength="%s" ', $this->max);

        }
        if($this->datalist !== null && !empty($this->datalist)) {
            $options .= sprintf(' list="%s"', $this->datalist['name']);
        }

        return trim($options);
    }

    protected function isInRange(mixed $value): bool {
        if($this->min !== null && $value!== null && strlen($value) < $this->min) {
            return false;
        }
        if($this->max !== null && $value !== null && strlen($value) > $this->max) {
            return false;
        }
        return true;
    }

    public function Validate(?Request $request): bool {
        if(! parent::Validate($request)) {
            return false;
        }
        
        try {
            $val = $this->getFormValue($request);
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

}

class FormTextElement extends FormInputElement {

    public function __construct($name, $value='', $multiple=false, $placeholder=null) {
        parent::__construct($name, $value, $multiple, $placeholder);
        $this->setType('textarea');
    }

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

    public function __construct($name, $value=0, $multiple=false, $placeholder=null) {
        parent::__construct($name, intval($value), $multiple, $placeholder);
        $this->setType('checkbox');
    }

    public function setValue(mixed $value)
    {
        $this->value = intval($value);
    }

    function html(?string $user=null, ?string $id_space=null) : string {
        $checked = "";
        if($this->value === "1" || $this->value == 1 || $this->value === true) {
            $checked = "checked";
        }
        return '  <input type="'.$this->type.'"'.$this->options($user, $id_space).' class="form-check-input '.$this->getClasses().'" id="'.$this->id.'" name="'.$this->name.'" '.$checked.' >'."\n";

    }

}

class FormCheckboxesElement extends FormBaseElement {
    private $boxes = [];

    public function __construct($name, $multiple=false, $placeholder=null) {
        parent::__construct($name, '', $multiple, $placeholder);
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
        //$html = '    <div class="">'."\n";
        $html = '';
        foreach ($this->boxes as $box) {
            $html .= '      <div class="row ">'."\n";

            $html .= '        <div class="col-md-12">'."\n";
            $html .= '          <div class="form-check">'."\n";
            $html .= '          '.$box->html($user, $id_space);
            $html .= '            <label class="form-check-label" for="'.$box->id.'">'.$box->label.'</label>'."\n";
            $html .= '          </div>'."\n";
            $html .= '        </div>'."\n";
            $html .= "      </div>\n";
        }
        $html .= "\n";
        //$html .= '    </div>'."\n";



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

class FormUploadElement extends FormBaseInputElement {

    public function __construct($name) {
        parent::__construct($name, null);
        $this->type = 'file';
    }

    public function setValue(mixed $value) {
        // can't set a value for a file
        $this->value = null;
    }

}

class FormIntegerElement extends FormFloatlement {

    public function __construct(string $name, int $value=0) {
        parent::__construct($name, $value);
        $this->type = 'number';
        $this->step = '1';
    }

    public function getFormValue(?Request $request): mixed {
        if($request !== null) {
            $val = $request->getParameterNoException($this->name);
            return intval($val);
        }
        return null;
    }

    public function setValue(mixed $value)
    {
        $this->value = intval($value);
    }

}

class FormFloatlement extends FormBaseInputElement {

    protected mixed $min = null;
    protected mixed $max = null;
    protected ?string $step = null;

    public function __construct(string $name, float $value=0) {
        parent::__construct($name, $value);
        $this->type = 'number';
        $this->step = 'any';
    }

    /**
     * Get common options
     */
    protected function options(?string $user=null, ?string $id_space=null): string {
        $options = parent::options($user, $id_space);

        if($this->min !== null) {
            $options .= sprintf(' min="%s" ', $this->min);

        }
        if($this->max !== null) {
            $options .= sprintf(' max="%s" ', $this->max);

        }
        if($this->step !== null) {
            $options .= sprintf(' step="%s" ', $this->step);
        }

        return trim($options);
    }

    public function setValue(mixed $value)
    {
        $this->value = floatval($value);
    }

    public function getFormValue(?Request $request): mixed {
        if($request !== null) {
            $val = $request->getParameterNoException($this->name);
            return floatval($val);
        }
        return null;
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
        if(! parent::Validate($request)) {
            return false;
        }
        
        try {
            $val = $this->getFormValue($request);
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
        if($min !== null && $this->value < $min) {
            $this->value = $min;
        }
        if($max !== null && $this->value > $max) {
            $this->value = $max;
        }
        if($step !== null) {
            $this->step = $step;
        }
        return $this;
    }

}

class FormHiddenElement extends FormBaseInputElement {

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


class FormDateElement extends FormDateTimeElement {


    public function __construct($name, $value='') {
        parent::__construct($name, $value);
        $this->type = 'date';
    }

}

class FormDateTimeElement extends FormBaseInputElement {

    protected mixed $min = null;
    protected mixed $max = null;

    public function __construct($name, $value='') {
        parent::__construct($name, $value);
        $this->type = 'datetime-local';
    }

    /**
     * @var mixed $min int or float min value
     * @var mixed $max int or float max value
     */
    public function setRange(mixed $min=null, mixed $max=null):FormBaseElement {
        if($min !== null) {
            $this->min = $min;
        }
        if($max !== null) {
            $this->max = $max;
        }
        return $this;
    }

    /**
     * Get common options
     */
    protected function options(?string $user=null, ?string $id_space=null): string {
        $options = parent::options($user, $id_space);

        if($this->min !== null) {
            $options .= sprintf(' min="%s" ', $this->min);

        }
        if($this->max !== null) {
            $options .= sprintf(' max="%s" ', $this->max);

        }
        return trim($options);
    }

}

class FormHourElement extends FormDateTimeElement {

    public function __construct($name, $value='') {
        parent::__construct($name, $value);
        $this->type = 'time';
    }

}

class FormEmailElement extends FormInputElement {

    public function __construct($name, $value='') {
        parent::__construct($name, $value);
        $this->type = 'email';
    }

}

class FormColorElement extends FormBaseInputElement {

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
    public function add($options) {
        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        } else {
        $this->options[] = $options;
        }
        return $this;
    }


   public function html(?string $user=null, ?string $id_space=null): string {
       $html = '<select class="form-control '.$this->getClasses().'" '.$this->options($user, $id_space).' id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'">'."\n";
       foreach($this->options as $option) {
           $ohtml =  $option->html();
           if($option->value == $this->value) {
               $ohtml = str_replace("<option", "<option selected", $ohtml);
           }
           $html .= $ohtml."\n";

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
    private ?string $url;
    // @var FormBaseElement[] $elts;
    private array $elts = [];

    private ?string $cancelUrl = null;
    private ?string $deleteUrl = null;
    private array $buttons = [];

    private static mixed $js = [];

    public function setRequest(Request $request) {
        $this->request = $request;
    }

    public function Javascript():string {
        $html =  "\n<script type=\"module\">\n";
        $html .= "import {FormControls} from '/externals/pfm/controls/formcontrols_script.js';\n";
        $html .= 'document.addEventListener("DOMContentLoaded", function(event) {'."\n";
        $html .= "  let control = new FormControls();\n";
        if(!isset(self::$js['_forms'])) {
            $html .= "  control.loadForms();\n";
            self::$js['_forms'] = 'control.loadForms();';
        }

        $jstoLoad = false;
        foreach ($this->elts as $elt) {
            foreach($elt->Javascript() as $key => $value) {
                if(isset(self::$js[$key])) {
                    continue;
                }
                $jstoLoad = true;
                $html .= "  // $key\n";
                $html .= "  $value\n";
                self::$js[$key] = $value;
            }
        }

        $html .= "});\n";
        $html .= "</script>\n";
        return $jstoLoad ?$html : '';
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
        return $this;
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
    public function Validate(?object $object=null, int $role=1): bool {
        if(!$this->isSubmitted()) {
            return false;
        }
        $isValid = true;
        foreach ($this->elts as $elt) {
            if(!$elt->Validate($this->request)) {
                $isValid = false;
                break;
            }
            if($elt->Required() !== null && $elt->Required() > $role) {
                // Just ignore the field, cannot be set by user
                continue;
            }
            $val = $elt->getFormValue($this->request);
            try {
                $elt->setValue($val);
                if ($object !== null && property_exists($object, $elt->getName())){
                        $object->{$elt->name} = $val;
                }
            } catch(Exception $e) {
                $elt->setError($e->getMessage());
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
            $html .= '<div class="row">'."\n";
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
    public function setValidationUrl($url) {
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
        if($readonly) {  $f->setReadOnly(); }
        if($checkUnicity) {  $f->setUnique($name); }
        if($isMandatory) {$f->setMandatory(); }
        if($suggestLogin) { $f->setSuggests(['firstname', 'name']); }
        
        $this->pfmform->add($f->setLabel($label));
    }

    /**
     * Password field
     * @param string $name Form variable name
     * @param string $label Field label
     * @param bool $isMandatory is mandatory field
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

        $f = new FormPasswordElement($name);
        if($isMandatory) {$f->setMandatory(); }
        $this->pfmform->add($f->setLabel($label));
    }

    /**
     * Add date field
     * @param string $name Form variable name
     * @param string $label Field label
     * @param bool $isMandatory is mandatory field
     * @param string $value default value
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

        $f = new FormDateElement($name, $value);
        if($isMandatory) {$f->setMandatory(); }
        $this->pfmform->add($f->setLabel($label));
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
        $f = new FormDateTimeElement($name, $value);
        if($isMandatory) {$f->setMandatory(); }
        $this->pfmform->add($f->setLabel($label));
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
        $hour = $value;
        if(is_array($value)) {
            $hour = implode(':', $value);
        }
        $f = new FormHourElement($name, $hour);
        if($isMandatory) {$f->setMandatory(); }
        $this->pfmform->add($f->setLabel($label));
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

        $f = new FormColorElement($name, $value);
        if($isMandatory) {$f->setMandatory(); }
        $this->pfmform->add($f->setLabel($label));
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

        $f = new FormEmailElement($name, $value);
        if($isMandatory) {$f->setMandatory(); }
        $this->pfmform->add($f->setLabel($label));
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

        $f = new FormIntegerElement($name, $value);
        if($isMandatory) {$f->setMandatory(); }
        $this->pfmform->add($f->setLabel($label));
    }

    /**
     * Add select input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param array $choices List of options names
     * @param array $choicesid List of options ids
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

        $f = new FormSelectElement($name, $value);
        $options = [];
        for($i=0;$i<count($choices);$i++){
            $options[] = new FormOptionElement($choices[$i], $choicesid[$i]);
        }
        $f->add($options)->setLabel($label);
        $this->pfmform->add($f);
    }
    
        /**
     * Add mandatory select input to the form
     * @param string $name Input name
     * @param string $label Input label
     * @param array $choices List of options names
     * @param array $choicesid List of options ids
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

        $f = new FormSelectElement($name, $value);
        $options = [];
        for($i=0;$i<count($choices);$i++){
            $options[] = new FormOptionElement($choices[$i], $choicesid[$i]);
        }
        $f->add($options);
        $this->pfmform->add($f->setMandatory()->setLabel($label));
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

        $f = new FormTextElement($name, $value);
        if($isMandatory) {$f->setMandatory()->setLabel($label); }
        $this->pfmform->add($f);
    }

    /**
     * Add a combo list 
     * @param string $label Field label
     * @param array $listNames List of choices name
     * @param array $listIds List of choices ids
     * @param array $values Default value
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

        $f = new FormCheckboxesElement($label);
        $options = [];
        for($i=0;$i<count($listNames);$i++){
            $option = new FormCheckboxElement($listIds[$i], $values[$i]);
            $options[] = $option->setLabel($listNames[$i]);
        }
        $f->add($options)->setLabel($label);
        $this->pfmform->add($f);
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

        $html = $this->pfmform->toHtml($lang);
        $html .= $this->pfmform->Javascript();
        return $html;

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
