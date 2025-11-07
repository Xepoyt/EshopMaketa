<?php

namespace App\Components\ProduktyComponent;

use App\Components\BaseComponent;
use Tracy\Debugger;

class ProduktyComponent extends BaseComponent
{
    public function handleKoupit(): void
    {
        $section = $this->getPresenter()->session->getSection("kosik");

        $section->set("pocet", $section->get("pocet") + 1);
        $section->set("celkem_czk", $section->get("celkem_czk") + 199.99);
        $section->set("celkem_eur", $section->get("celkem_eur") + 7.99);


        if ($this->getPresenter()->isAjax()) {
            Debugger::barDump('AJAX request - redrawing snippets', 'After koupit');
            $this->presenter->redrawControl('kosikPocet');
            $this->presenter->redrawControl('kosikCelkemCZK');
            $this->presenter->redrawControl('kosikCelkemEUR');
        } else {
            $this->getPresenter()->redirect('this');
        }
    }
}