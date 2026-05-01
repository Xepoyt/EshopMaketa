<?php
namespace App\Facades;

use App\Models\ProduktModel\ProduktModel;
use App\Services\ProduktyService;
use App\Services\KosikService;

use App\Exceptions\NedostupnaVariantaException;

use Tracy\Debugger;

class NakupFacade
{
    private ProduktyService $produktyService;
    private KosikService $kosikService;
    private ProduktModel $produktModel;

    public function __construct(ProduktyService $produktyService, KosikService $kosikService, ProduktModel $produktModel)
    {
        $this->produktyService = $produktyService;
        $this->kosikService = $kosikService;
        $this->produktModel = $produktModel;
    }

    public function pridejDoKosiku(int $produktId, array $varianty, int $mnozstvi = 1): void
    {
        $vysledek = $this->produktyService->dostupnostKombinace($produktId, $varianty);
        Debugger::barDump($vysledek, "Dostupnost kombinace ve VariantyFormComponent");
        $kombinaceId = $vysledek['kombinaceId'];

        if(!$kombinaceId || $vysledek['ks'] == 0){
            throw new NedostupnaVariantaException();
        }

        $produkt = $this->produktModel->najit('id', $produktId);
        if(!$produkt){
            throw new NedostupnaVariantaException("Kritická chyba: Produkt s ID $produktId nebyl nalezen.");
        }

        $max = $this->produktyService->kombinace[$kombinaceId] ?? 0;

        $this->kosikService->pridatPolozku($produkt->id, $produkt->nazev, $produkt->cena100, $kombinaceId, $max, $mnozstvi);

    }
}