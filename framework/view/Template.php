<?php

namespace FM\Framework\view;

use FM\Framework\Application;
use FM\Framework\url\Url;

class Template {

    private $variables;
    private $renderer = null;
    private $renderView = true;

    public function __construct() {
        $this->variables = array();
        $this->renderer = Application::singleton('view');
    }

    public function render() {
        if($this->renderView) {
            $this->set('view', Application::singleton('FM\Framework\view\ViewHelper'));
            $this->set('url', URL::getBaseURL());
            $this->parentTemplates();
            echo $this->renderChildes();
        } else {
            return;
        }
    }

    private function parentTemplates() {
        $this->renderer->load('base.html');
    }

    private function renderChildes() {
        $folder = Application::singleton('FM\Framework\Dispatcher')->getController();
        $file = Application::singleton('FM\Framework\Dispatcher')->getAction();
        $template = $this->renderer->load($folder.'/'.$file.'.html');
        return $template->render($this->prepVars());
    }

    private function prepVars() {
        return $this->variables;
    }

    public function set($name, $value) {
        $this->variables[$name] = $value;
    }

    public function noRenderer() {
        $this->renderView = false;
    }



}
