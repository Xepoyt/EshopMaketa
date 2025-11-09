<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use App\Components\KosikNahledComponent\ProduktyComponent as KosikNahledComponentProduktyComponent;
use Nette;
use Nette\ComponentModel\IComponent;
use App\Components\ProduktyComponent\ProduktyComponent;
use App\Components\KosikNahledComponent\KosikNahledComponent;
use Tracy\Debugger;

final class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Http\SessionSection */
    public Nette\Http\SessionSection $section;

    function beforeRender()
    {
        parent::beforeRender();

        $this->section = $this->session->getSection("kosik");
        $this->section->setExpiration('20 minutes');

        if($this->section->get("seznam") === null){
            $this->section->set("seznam", []);
        }
    }
    
    function createComponentProdukty(): IComponent
    {
        return new ProduktyComponent();
    }

    function createComponentKosikNahled(): IComponent
    {
        return new KosikNahledComponent();
    }
}

