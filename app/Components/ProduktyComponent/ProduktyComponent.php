<?php

namespace App\Components\ProduktyComponent;

use App\Components\BaseComponent;
use Tracy\Debugger;

class ProduktyComponent extends BaseComponent
{
    public function handleKoupit(int $id): void
    {
        $section = $this->getPresenter()->session->getSection("kosik");

        if(!$section->get("seznam")) {
            $section->set("seznam", []);
        }

        switch ($id) {
            case 1:
                $section->set("seznam", array_merge($section->get("seznam"), [[$id, 'Produkt 1', 199.99]]));
                break;
            case 2:
                $section->set("seznam", array_merge($section->get("seznam"), [[$id, 'Produkt 2', 299.99]]));
                break;
            case 3:
                $section->set("seznam", array_merge($section->get("seznam"), [[$id, 'Produkt 3', 399.99]]));
                break;

        }

        if ($this->getPresenter()->isAjax()) {
            Debugger::barDump($section->get("seznam"), 'After koupit');

            $this->presenter->getComponent('kosikNahled')->redrawControl(); //neni realne chyba
        } else {
            $this->getPresenter()->redirect('this');
        }
    }
}