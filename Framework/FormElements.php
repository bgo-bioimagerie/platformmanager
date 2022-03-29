<?php
/**
 * Examples
 * 
 * <?php
*  require_once 'Framework/FormElements.php';
*
*  $e = new FormTypeaheadElement('test', 'test', '', 'some test');
*  $e->setOptions(['1' => 'opt1', '2' => 'opt2', '3' => "other"]);
*  echo $e->toHtml();
*  $b = new FormInputElement('test2', 'test2', '222', 'test other');
*  echo $b->toHtml();
*  ?>
 */
abstract class FormElement {

    protected string $type = 'text';
    protected ?string $id = '';
    protected ?string $name = '';
    protected ?string $label = '';
    protected ?string $placeholder = '';
    protected $value = '';
    protected bool $mandatory = false;
    protected bool $disabled = false;
    protected string $aboutText = '';
    protected string $errorText = '';

    protected string $labelSize = 'col-md-2'; // bootstrap col size
    protected string $inputSize = 'col-md-10'; // bootstrap col size

    function __construct(string $type) {
            $this->type = $type;
    }

    public function getType(): string {
        return $this->type;
    }

    public  function setMandatory(bool $is_mandatory=true) {
        $this->mandatory = $is_mandatory;
    }

    public  function isMandatory():bool {
        return $this->mandatory;
    }

    public  function setDisabled(bool $is_disabled=true) {
        $this->disabled = $is_disabled;
    }

    public  function isDisabled():bool {
        return $this->disabled;
    }

    public  function setPlaceholder(string $placeholder) {
        $this->placeholder = $placeholder;
    }

    public  function getPlaceholder():string {
        return $this->placeholder;
    }

    public  function setValue($value) {
        $this->value = $value;
    }

    public  function getValue():mixed {
        return $this->value;
    }

    public  function setLabel(string $label) {
        $this->label = $label;
    }

    public  function getLabel():string {
        return $this->label;
    }

    public  function setName(string $name) {
        $this->name = $name;
    }

    public  function getName():string {
        return $this->name;
    }

    public  function setAbout(string $about) {
        $this->about = $about;
    }

    public  function getAbout():string {
        return $this->about;
    }

    public  function setError(string $error) {
        $this->errorText = $error;
    }

    public  function clearError() {
        $this->errorText = '';
    }

    public  function getError():string {
        return $this->errorText;
    }

    public function setColSize(string $labelSize, string $inputSize) {
        $this->labelSize = $labelSize ? 'col-md-'.$labelSize : '';
        $this->inputSize = $inputSize ? 'col-md-'.$inputSize : '';
    }

    public function getLabelSize():string {
        return $this->labelSize;
    }

    public function getInputSize():string {
        return $this->inputSize;
    }

    public function htmlContraints(): string {
        $html = '';
        if ($this->isMandatory()) {
            $html  .= ' required ';
        }
        if($this->isDisabled()) {
            $html .= ' readonly ';
        }
        if($this->getPlaceholder()) {
            $html .= ' placeholder="'.$this->getPlaceholder().'"';
        }
        return $html;
    }

    protected function about(): string {
        if(!$this->aboutText) {
            return '';
        }
        return sprintf('<div class="col-12"><small>%s</small></div>', $this->aboutText);
    }

    protected function error(): string {
        if(!$this->errorText) {
            return '';
        }
        return sprintf('<div class="col-12 alert alert-danger">%s</div>', $this->errorText);
    }

    protected function htmlLabel():string {
        if($this->getLabelSize() == 'col-0') {
            return '';
        }
        $reqTxt = '';
        if ($this->isMandatory()) {
            $reqTxt = ' *';
        }
        Configuration::getLogger()->error('???????,',['l' => $this->label]);
        return <<<HTML
        <label class="{$this->getLabelSize()} col-form-label">{$this->label}{$reqTxt}</label>
        HTML;

    }

    public abstract function toHtml(): string;


}

class FormColorElement extends FormInputElement {
    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'color';
    }
}

class FormEmailElement extends FormInputElement {
    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'email';
    }
}

class FormPasswordElement extends FormInputElement {
    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'password';
    }
}

class FormDateElement extends FormInputElement {
    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'date';
    }
}

class FormIntegerElement extends FormInputElement {

    private $min = -1;
    private $max = -1;
    private string $step = "1";

    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'number';
    }


    /**
     * Set min/max time range
     * Set -1 if no contraints
     */
    function setRange(int $min, int $max) {
        $this->min = $min;
        $this->max = $max;
    }

    public function htmlContraints(): string {
        $html = parent::htmlContraints();
        if($this->max > -1) {
            $html .= sprintf(' min="%s"" ', $this->max);
        }
        if($this->min > -1) {
            $html .= sprintf(' min="%s"" ', $this->min);
        }
        $html .= sprintf(' step="%s" ', $this->step);
        return $html;
    }
}


class FormFloatElement extends FormIntegerElement {
    public function setStep(string $step) {
        $this->step = $step;
    }
}

class FormTimeElement extends FormInputElement {

    private string $min = '';
    private string $max = '';

    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'time';
    }


    /**
     * Set min/max time range in string format 13:45
     * Set empty string if no contraints
     */
    function setRange(string $min, string $max) {
        $this->min = $min;
        $this->max = $max;
    }

    public function htmlContraints(): string {
        $html = parent::htmlContraints();
        if($this->max) {
            $html .= ' max="'.$this->max.'" ';
        }
        if($this->min) {
            $html .= ' min="'.$this->min.'" ';
        }
        return $html;
    }
}

class FormDateTimeElement extends FormInputElement {

    private $date_name = '';
    private $date_value = '';
    private $time_name = '';
    private $time_value = '';

    function __construct(string $id, string $date_name, string $date_value, string $time_name, string $time_value, string $label='') {
        parent::__construct($id, '', '', $label);
        $this->type = 'datetime';
        $this->date_name = $date_name;
        $this->date_value = $date_value;
        $this->time_name = $time_name;
        $this->time_value = $time_value;
    }

    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
            <div class="col-12 {$this->getInputSize()}">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <input
                        id="{$this->id}Date"
                        class="form-control"
                        type="date"
                        name="{$this->date_name}"
                        value="{$this->date_value}"
                        {$this->htmlContraints()}/>
                    </div>
                    <div class="col-12 col-md-6">
                        <input
                        id="{$this->id}Time"
                        class="form-control"
                        type="time"
                        name="{$this->time_name}"
                        value="{$this->time_value}"
                        {$this->htmlContraints()}/>
                    </div>
                </div>
            </div>
            {$this->about()}
            {$this->error()}
        </div>
        HTML;
    }
}

class FormCommentElement extends FormElement {
    function __construct(string $label) {
        parent::__construct('text');
        $this->label = $label;
    }

    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
        </div>
        HTML;
    }
}

class FormHiddenElement extends FormInputElement {
    function __construct(string $id, string $name, string $value) {
        parent::__construct($id, $name, $value, '');
        $this->type = 'hidden';
    }
}

class FormTextAreaElement extends FormInputElement {


    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'textarea';
    }

    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
            <div class="col-12 {$this->getInputSize()}">
                <textarea
                    id="{$this->id}"
                    class="form-control"
                    name="{$this->name}"
                    {$this->htmlContraints()}>
                    {$this->value}
                </textarea>
            </div>
            {$this->about()}
            {$this->error()}
        </div>
        HTML;
    }
}

class FormInputElement extends FormElement {

    private bool $unique = false;

    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct('text');
        $this->id = $id;
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
    }

    public function htmlContraints(): string {
        $html = parent::htmlContraints();
        if($this->isUnique()) {
            $html .= ' unique ';
        }
        return $html;
    }

    function setUnique(bool $unique=true) {
        $this->unique = $unique;
    }

    function isUnique():bool {
        return $this->unique;
    }

    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
            <div class="col-12 {$this->getInputSize()}">
                <input
                id="{$this->id}"
                class="form-control"
                type="{$this->type}"
                name="{$this->name}"
                value="{$this->value}"
                {$this->htmlContraints()}/>
            </div>
            {$this->about()}
            {$this->error()}
        </div>
        HTML;
    }
}

class FormUploadElement extends FormInputElement {

    function __construct(string $id, string $name, string $value, string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'file';
    }

    function img(): string {
        if(!$this->value) {
            return '';
        }
        return '<img src="'.$this->value.'" width="100">';
    }

    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
            <div class="col-12 {$this->getInputSize()}">
                {$this->img()}
                <input
                id="{$this->id}"
                class="form-control"
                type="{$this->type}"
                name="{$this->name}"
                {$this->htmlContraints()}/>
            </div>
            {$this->about()}
            {$this->error()}
        </div>
        HTML;
    }
}

class FormDownloadElement extends FormElement {

    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
            <div class="col-12 {$this->getInputSize()}">
                <input name="{$this->name}" type="hidden" value="{$this->value}">";
                <input
                    type="submit"
                    id="{$this->id}submit"
                    class="btn btn-outline-dark"
                    value="{$this->label}"
                    name="{$this->name}"
                />
            </div>
            {$this->about()}
            {$this->error()}
        </div>
        HTML;
    }
}


class FormSelectElement extends FormInputElement {

    private array $options = [];

    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'select';
    }

    /**
     * Set options in format [['name' => 'value', ...]]
     */
    function setOptions(array $options) {
        $this->options = $options;
    }

    function getOptions():string {
        $html = '';
        foreach ($this->options as $key => $value) {
            $selected = '';
            if($value == $this->value) {
                $selected = ' selected="selected" ';
            }
            $html .= <<<HTML
            <option value="{$value}" {$selected}>{$key}</option>
            HTML;
        }
        return $html;
    }


    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
            <div class="col-12 {$this->getInputSize()}">
                <select
                    id="{$this->id}"
                    class="form-select"
                    type="{$this->type}"
                    name="{$this->name}"
                    value="{$this->value}"
                    {$this->htmlContraints()}
                >
                {$this->getOptions()}
                </select>
            </div>
            {$this->about()}
            {$this->error()}
        </div>
        HTML;
    }
}

class FormChoicesElement extends FormInputElement {

    private array $options = [];

    function __construct(string $id, string $name, mixed $value='', string $label='') {
        parent::__construct($id, $name, $value, $label);
        $this->type = 'choices';
    }

    /**
     * Set options in format [['id' => 'label', ...]]
     */
    function setOptions(array $options) {
        $this->options = $options;
    }

    function getOptions():string {
        $html = '';
        foreach ($this->options as $key => $value) {
            $selected = '';
            if($value == $this->key) {
                $selected = ' checked" ';
            }
            $html .= <<<HTML
            <div id="form_blk_{$key}" class="checkbox"><label><input class="form-check-input" type="checkbox" name="{$key}" {$selected}>{$value}</label></div>
            HTML;
        }
        return $html;
    }


    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
            <div class="col-12 {$this->getInputSize()}">
                {$this->getOptions()}
            </div>
            {$this->about()}
            {$this->error()}
        </div>
        HTML;
    }
}

class FormButtonsElement extends FormElement {

    private string $offset = '';
    public function setOffset(string $offset) {
        if(!$offset) {
            return;
        }
        $this->offset = 'offset-'.$offset;
    }

    private $formId = '';
    private array $buttons = [];

    /**
     * Set of buttons
     * 
     * button define a name to display, url to go to and optional new to open in new tab and optional class type (bootstrap class, btn-primary, btn-danger, ..)
     * buttons =  ['save' => ['name' => 'go to'], 'cancel' => ['name' => 'cancel', 'url' => '/x/y'], ...]
     */
    function __construct(string $formId, $buttons=[],) {
        parent::__construct('button');
        $this->formId = $formId;
        $this->buttons = $buttons;
    }

    function toHtml(): string
    {
        $html = '<div class="mb-3 row">';
        $html .= '<div class="col-12 '. $this->getInputSize() . ' '. $this->offset . '">';
        foreach ($this->buttons as $name => $button) {
            if($name == 'save') {
                $html .= '<input type="submit" id="' . $this->formId . 'submit" class="m-2 btn btn-primary" value="' . $button['name'] . '" />';
                continue;
            }
            if($name == 'delete') {
                $html .= '<input type="submit" id="' . $this->formId . 'delete" class="m-2 btn btn-primary" value="' . $button['name'] . '" />';
                continue;
            }
            if (isset($button['new']) && $button['new']) {
                $html .= '<button type="button" onclick="window.open(\'' . $button["url"] . '\')" class="m-2 btn ' . $button["class"] ?? '' . '" >'.$button['name'].'</button>';
            } else {
                $html .= '<button type="button" onclick="location.href=\'' . $button["url"] . '\'" class="m-2 btn ' . $button["class"] ?? '' . '" >'.$button['name'].'</button>';
            }
        }
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }
}

class FormSeparatorElement extends FormElement {

    private int $level = 3;

    function __construct($label, $level=3) {
        $this->level = $level;
        $this->label = $label;
    }

    function toHtml(): string
    {
        return <<<HTML
        <div class="mb-3 row">
        <div class="col-12"><h{$this->level}>{$this->label}</h{$this->level}></div>
        </div>
        HTML;
    }
}

class FormTypeaheadElement extends FormInputElement {

    private array $options = [];

    /**
     * List of options to select ['id' => 'name', ...]
     */
    public function setOptions(array $options) {
        $this->options = $options;
    }

    private function getOptions(): string {
        $html = sprintf('<datalist id="%s_ta_list">', $this->id);
        foreach ($this->options as $id => $name) {
            $html .= sprintf('<option value="%s"/>', $name)."\n";
        }
        $html .= '</datalist>'."\n";
        return $html;
    }

    private function getHash(): string {
        $html = 'let '.$this->id.' = ';
        $elts = [];
        foreach ($this->options as $id => $name) {
            $elts[$name] = $id;
            if(!$id){
                $elts[$name] = $name;
            }
        }
        $html .= json_encode($elts).";\n";
        return $html;
    }

    function toHtml(): string {
        return <<<HTML
        <div id="form_blk_{$this->name}" class="mb-3 row">
            {$this->htmlLabel()}
            <div class="col-12 {$this->getInputSize()}">
                <input
                    id="{$this->id}"
                    class="form-control"
                    type="hidden"
                    name="{$this->name}"
                    value="{$this->value}"
                />
                {$this->getOptions()}
                <input
                id="{$this->id}_ta_elt"
                class="form-control"
                type="{$this->type}"
                value="{$this->value}"
                list="{$this->id}_ta_list"
                onchange="ta_{$this->id}()"
                {$this->htmlContraints()}/>
            </div>
            {$this->about()}
            {$this->error()}
            <script>
                {$this->getHash()}
                function ta_{$this->id}() {
                    let elt_select = document.getElementById('{$this->id}_ta_elt');
                    let value = elt_select.value;
                    let elt = document.getElementById('{$this->id}');
                    elt.value = {$this->id}[value]
                }
            </script>
        </div>
        HTML;
    }
}

?>
