<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use App\Components\KosikNahledComponent\ProduktyComponent as KosikNahledComponentProduktyComponent;
use Nette;
use Nette\ComponentModel\IComponent;
use App\Components\ProduktyComponent\ProduktyComponent;

final class HomePresenter extends Nette\Application\UI\Presenter
{
    function beforeRender()
    {
        parent::beforeRender();
    }
    
    function createComponentProdukty(): IComponent
    {
        return new ProduktyComponent();
    }
}

