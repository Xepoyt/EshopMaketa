<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use App\Components\KosikNahledComponent\ProduktyComponent as KosikNahledComponentProduktyComponent;
use Nette;
use Nette\ComponentModel\IComponent;
use App\Components\ProduktyComponent\ProduktyComponent;
use Tracy\Debugger;

final class HomePresenter extends Nette\Application\UI\Presenter
{
    
    public int $kosikPocet = 0;
    public float $kosikCelkemCZK = 0.0;
    public float $kosikCelkemEUR = 0.0;

    function beforeRender()
    {
        parent::beforeRender();
    }

    function renderDefault(): void
    {
        $section = $this->session->getSection("kosik");
        if($section->get("pocet") === null){
            $section->set("pocet", 0);
        }
        if($section->get("celkem_czk") === null){
            $section->set("celkem_czk", 0.0);
        }
        if($section->get("celkem_eur") === null){
            $section->set("celkem_eur", 0.0);
        }
        $this->template->kosikPocet = $section->get("pocet");
        $this->template->kosikCelkemCZK = number_format($section->get("celkem_czk"), 2, ',', ' ');
        $this->template->kosikCelkemEUR = number_format($section->get("celkem_eur"), 2, ',', ' ');
    }
    
    function createComponentProdukty(): IComponent
    {
        return new ProduktyComponent();
    }
}

