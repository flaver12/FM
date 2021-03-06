<?php

namespace Solaria\Framework\View;

use Solaria\Framework\Application;
use Solaria\Framework\Url\Url;
use Exception;

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
            $this->set('view', Application::singleton('Solaria\Framework\View\ViewHelper'));
            $this->set('flashSession', Application::singleton('Solaria\Framework\View\Flash\SessionFlash'));
            $this->set('url', Url::getBaseURL());
            $this->parentTemplates();
            echo $this->renderChildes();
            return;
        } else {
            return;
        }
    }

    private function parentTemplates() {
        $this->renderer->load('base.html');
    }

    private function renderChildes() {
        $folder = Application::singleton('Solaria\Framework\Dispatcher')->getController();
        $file = Application::singleton('Solaria\Framework\Dispatcher')->getAction();
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
