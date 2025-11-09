<?php

namespace App\Components;

abstract class BaseComponent extends \Nette\Application\UI\Control
{
    /** @var string[] */
    protected array $parameters = [];

    protected function getTemplateFile(){
        return str_replace(".php", ".latte", $this->getReflection()->getFileName());
    }

    protected function putParametersIntoTemplate(){
        foreach ($this->parameters as $key) {
            if(property_exists($this, $key)){
                $this->template->$key = $this->$key;
            }
        }
    }

    function beforeRender(){
        parent::beforeRender();
    }

    function render(){
        $this->template->setFile($this->getTemplateFile());
        $this->putParametersIntoTemplate();
        $this->template->render();
    }
}