<?php
namespace FM\Framework\View\Forms\Fields;

use FM\Framework\View\Forms\Fields\FieldInterface;

class InputField implements FieldInterface {

    protected $name;
    protected $labelName;
    private $params;

    public function __construct($labelName, $name, $params = array()) {
        $this->labelName = $labelName;
        $this->name = $name;
        $this->params = $params;
    }

    public function render() {
        //htmls stuff here
        echo '<label>'.$this->labelName.'</label> <input name="'.$this->name.'" class="'.$this->params['class'].'" type="'.$this->params['type'].'"value="'.$this->params['value'].'" />';

    }

}