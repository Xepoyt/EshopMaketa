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
        $section = $this->getPresenter()->session->getSection("kosik");
        Debugger::barDump($section->get("seznam"), 'After render');
    }

    function nastavKosik(){
        $section = $this->getPresenter()->session->getSection("kosik");
        Debugger::barDump($section->get("seznam"), 'Before render');
        $this->kosikPocet = count($section->get("seznam"));

        $celkem = 0.0;
        foreach ($section->get("seznam") as $polozka) {
            $celkem += $polozka['produkt_cena'];
        }
        $this->kosikCelkemCZK = number_format($celkem, 2, ',', ' ');
        $this->kosikCelkemEUR = number_format(MenaService::CZKtoEUR($celkem), 2, ',', ' ');
        Debugger::barDump($this->kosikPocet, 'kosikPocet');
        Debugger::barDump($this->kosikCelkemCZK, 'kosik CZK');
        Debugger::barDump($this->kosikCelkemEUR, 'kosik EUR');
    }

    
}