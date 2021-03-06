<?php
namespace Solaria\App\Forms;

use Solaria\Framework\View\Forms\Form;
use Solaria\Framework\View\Forms\Fields\InputField;
use Solaria\Framework\View\Forms\Fields\Button;
use Solaria\Framework\View\Forms\Fields\SelectField;
use Solaria\Framework\View\Forms\Fields\CheckboxField;

class CreateTopicForm extends Form {

    public function __construct($cats, $groups) {
        $this->setMethod('POST');
        $this->setFormClass('.form-inline ');
        $this->setURL('admin/create-topic');
        $this->addItem(new InputField('Name', 'name', array('class' => 'bbcode-form-topic-id',  'type' => 'text', 'value' => '',)));
        $this->addItem(new SelectField('Kategorie', 'category', $cats));
        $this->addItem(new CheckboxField('Wer darf es sehen', 'allow_see', $groups));
        $this->addItem(new Button('Erstellen'));
    }
}
