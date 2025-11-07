<?php

namespace App\Components;

abstract class BaseComponent extends \Nette\Application\UI\Control
{
    protected function getTemplateFile(){
        return str_replace(".php", ".latte", $this->getReflection()->getFileName());
    }

    protected function putParametersIntoTemplate(){

    }

    function render(...$parameters){
        $this->template->setFile($this->getTemplateFile());
        $this->putParametersIntoTemplate(...$parameters);
        $this->template->render();
    }
}