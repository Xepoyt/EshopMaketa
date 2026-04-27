<?php

namespace App\Components\KosikNahledComponent;
use App\Components\BaseComponent;
use Tracy\Debugger;
use App\Services\MenaService;
use App\Services\KosikService;

class KosikNahledComponent extends BaseComponent
{
    public int $kosikPocet = 0;
    public string $kosikCelkemCZK = '0,00';
    public string $kosikCelkemEUR = '0,00';

    private MenaService $menaService;

    function __construct(MenaService $menaService, KosikService $kosikService){
        $this->menaService = $menaService;
        $this->kosikService = $kosikService;
        $this->parameters = ['kosikPocet', 'kosikCelkemCZK', 'kosikCelkemEUR'];
    }

    function render()
    {
        $this->nastavKosik();
        parent::render();
    }

    function nastavKosik(){
        $seznam = $this->kosikService->getSeznam();

        $celkem = 0.0;
        foreach ($seznam as $polozka) {
            $celkem += $polozka['produkt_cena'] * $polozka['ks'];
            $this->kosikPocet += $polozka['ks'];
        }
        $this->kosikCelkemCZK = number_format($celkem, 2, ',', ' ');
        $this->kosikCelkemEUR = number_format($this->menaService->CZKtoEUR($celkem), 2, ',', ' ');
    }

    
}