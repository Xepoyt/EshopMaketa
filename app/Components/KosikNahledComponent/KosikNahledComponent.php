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
    private KosikService $kosikService;

    function __construct(MenaService $menaService, KosikService $kosikService){
        $this->menaService = $menaService;
        $this->kosikService = $kosikService;
        $this->parameters = ['kosikPocet', 'kosikCelkemCZK', 'kosikCelkemEUR'];
    }

    function render()
    {
        $celkovaCena = $this->kosikService->getCelkovaCena();
        $this->kosikPocet = $this->kosikService->getCelkemKS();
        $this->kosikCelkemCZK = number_format($celkovaCena, 2, ',', ' ');
        $this->kosikCelkemEUR = number_format($this->menaService->CZKtoEUR($celkovaCena), 2, ',', ' ');
        parent::render();
    }    
}