<?php

namespace App\Components\KosikNahledComponent;
use App\Components\BaseComponent;
use Tracy\Debugger;
use App\Services\MenaService;

class KosikNahledComponent extends BaseComponent
{
    public int $kosikPocet = 0;
    public string $kosikCelkemCZK = '0,00';
    public string $kosikCelkemEUR = '0,00';

    function __construct(){
        $this->parameters = ['kosikPocet', 'kosikCelkemCZK', 'kosikCelkemEUR'];
    }

    function render()
    {
        $this->nastavKosik();
        parent::render();
    }

    function nastavKosik(){
        $section = $this->presenter->session->getSection("kosik");

        $celkem = 0.0;
        foreach ($section->get("seznam") as $polozka) {
            $celkem += $polozka['produkt_cena'] * $polozka['ks'];
            $this->kosikPocet += $polozka['ks'];
        }
        $this->kosikCelkemCZK = number_format($celkem, 2, ',', ' ');
        $this->kosikCelkemEUR = number_format(MenaService::CZKtoEUR($celkem), 2, ',', ' ');
    }

    
}