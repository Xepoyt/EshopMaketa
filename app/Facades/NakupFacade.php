<?php
namespace App\Facades;

use App\Models\ProduktModel\ProduktModel;
use App\Models\KombinaceModel\KombinaceModel;
use App\Services\VariantyService;
use App\Services\KosikService;

use App\Exceptions\NedostupnaVariantaException;

use Tracy\Debugger;

class NakupFacade
{
    private VariantyService $variantyService;
    private KosikService $kosikService;
    private ProduktModel $produktModel;
    private KombinaceModel $kombinaceModel;

    public function __construct(VariantyService $variantyService, KosikService $kosikService, ProduktModel $produktModel, KombinaceModel $kombinaceModel)
    {
        $this->variantyService = $variantyService;
        $this->kosikService = $kosikService;
        $this->produktModel = $produktModel;
        $this->kombinaceModel = $kombinaceModel;
    }

    public function pridejDoKosiku(int $produktId, array $varianty, int $mnozstvi = 1): void
    {
        $vysledek = $this->variantyService->dostupnostKombinace($produktId, $varianty);
        Debugger::barDump($vysledek, "Dostupnost kombinace ve VariantyFormComponent");
        $kombinaceId = $vysledek['kombinaceId'];

        if(!$kombinaceId || $vysledek['ks'] == 0){
            throw new NedostupnaVariantaException();
        }

        $produkt = $this->produktModel->najit('id', $produktId);
        if(!$produkt){
            throw new NedostupnaVariantaException("Kritická chyba: Produkt s ID $produktId nebyl nalezen.");
        }

        $max = $this->kombinaceModel->najit('id', $kombinaceId)->kusy;

        $this->kosikService->pridatPolozku($produkt->id, $produkt->nazev, $produkt->cena100, $kombinaceId, $max, $mnozstvi);

    }
}