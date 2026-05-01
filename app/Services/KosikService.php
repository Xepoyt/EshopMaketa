<?php
namespace App\Services;

use Nette\Http\Session;
use Nette\Http\SessionSection;

use App\Models\ProduktVariantaKombinaceModel\ProduktVariantaKombinaceModel;
use App\Models\KombinaceModel\KombinaceModel;

use App\Services\StitkyService;

use Tracy\Debugger;

class KosikService
{
    private SessionSection $section;
    private ProduktVariantaKombinaceModel $produktVariantaKombinaceModel;
    private KombinaceModel $kombinaceModel;
    private StitkyService $stitkyService;

    public function __construct(Session $session, ProduktVariantaKombinaceModel $produktVariantaKombinaceModel, KombinaceModel $kombinaceModel, StitkyService $stitkyService)
    {
        $this->section = $session->getSection('kosik');
        $this->section->setExpiration('20 minutes');
        $this->produktVariantaKombinaceModel = $produktVariantaKombinaceModel;
        $this->kombinaceModel = $kombinaceModel;
        $this->stitkyService = $stitkyService;
    }

    public function getSeznam(): array
    {
        return $this->section->get('seznam') ?? [];
    }
    public function setSeznam(array $seznam): void
    {
        $this->section->set('seznam', $seznam);
    }

    public function jePrazdny(): bool
    {
        return empty($this->getSeznam());
    }

    public function vymazat(): void
    {
        $this->section->remove('seznam');
    }

    public function najdiPolozku(int $kombinaceId): ?array
    {
        $seznam = $this->getSeznam();
        $polozka = array_filter($seznam, fn($item) => $item['kombinace_id'] == $kombinaceId);
        return reset($polozka) ?: null;
    }

    public function getCelkovaCena(): float
    {
        $celkem = 0.0;
        foreach ($this->getSeznam() as $polozka) {
            $celkem += $polozka['produkt_cena'] * $polozka['ks'];
        }
        return $celkem;
    }

    public function getCelkemKS(): int
    {
        $celkemKS = 0;
        foreach ($this->getSeznam() as $polozka) {
            $celkemKS += $polozka['ks'];
        }
        return $celkemKS;
    }

    public function pridatPolozku(int $produktId, string $produktNazev, int $produktCena100, int $kombinaceId, int $kusy, int $max): void
    {
        Debugger::barDump($this->getSeznam(), 'Seznam před přidáním položky');
        Debugger::barDump(['produktId' => $produktId, 'produktNazev' => $produktNazev, 'produktCena100' => $produktCena100, 'kombinaceId' => $kombinaceId, 'kusy' => $kusy, 'max' => $max], 'Přidávaná položka');
        if($this->jePrazdny()){
            $this->setSeznam([['produkt_id' => $produktId, 'produkt_nazev' => $produktNazev, 'produkt_cena' => $produktCena100 / 100, 'kombinace_id' => $kombinaceId, 'ks' => 1]]);
            return;
        }
        $seznam = $this->getSeznam();

        $polozka = $this->najdiPolozku($kombinaceId);

        if(!$polozka){
            $this->setSeznam(array_merge($seznam, [['produkt_id' => $produktId, 'produkt_nazev' => $produktNazev, 'produkt_cena' => $produktCena100 / 100, 'kombinace_id' => $kombinaceId, 'ks' => 1]]));
        }
        else{
            $novaKs = $polozka['ks'] + 1;
            if($novaKs > $max){
                $novaKs = $max;
            }
            $novaPolozka = ['produkt_id' => $produktId, 'produkt_nazev' => $produktNazev, 'produkt_cena' => $produktCena100 / 100, 'kombinace_id' => $kombinaceId, 'ks' => $novaKs];

            //odstranime starou polozku
            $seznamBezStare = array_filter($seznam, fn($item) => $item['kombinace_id'] != $kombinaceId);
            //pridame novou polozku
            $seznamBezStare[] = $novaPolozka;

            $this->setSeznam($seznamBezStare);
        }
    }

    public function odecistPolozku(int $kombinaceId, int $kusy = 1): void
    {
        $seznam = $this->getSeznam();

        foreach($seznam as $key => $polozka){
            if($polozka['kombinace_id'] == $kombinaceId){
                $novaKs = $polozka['ks'] - $kusy;
                if($novaKs < 0){
                    $novaKs = 0;
                }
                $seznam[$key]['ks'] = $novaKs;
                break;
            }
        }

        $this->setSeznam($seznam);
    }

    public function pricistPolozku(int $kombinaceId, int $kusy = 1): void
    {
        $seznam = $this->getSeznam();
        $max = $this->kombinaceModel->najit('id', $kombinaceId)->kusy ?? 0;

        foreach($seznam as $key => $polozka){
            if($polozka['kombinace_id'] == $kombinaceId){
                $novaKs = $polozka['ks'] + $kusy;
                if($novaKs > $max){
                    $novaKs = $max;
                }
                $seznam[$key]['ks'] = $novaKs;
                break;
            }
        }

        $this->setSeznam($seznam);
    }

    public function getObsahKosiku(): array
    {
        $seznam = $this->getSeznam();
        if(empty($seznam)){
            return [];
        }
        $kombinaceIds = array_column($seznam, 'kombinace_id');
        $produktIds = array_unique(array_column($seznam, 'produkt_id'));
        $variantyvDB = $this->produktVariantaKombinaceModel->najdiVariantyKombinace($kombinaceIds);
        $kombinacevDB = $this->kombinaceModel->najitAll('id', $kombinaceIds);
        $stitkyvDB = $this->stitkyService->najdiStitkyProProdukty($produktIds);
        $skladem = [];
        foreach($kombinacevDB as $kombinace){
            $skladem[$kombinace->id] = $kombinace->kusy;
        }

        foreach($seznam as $key => $polozka){
            $kombinaceId = $polozka['kombinace_id'];
            $produktId = $polozka['produkt_id'];
            $seznam[$key]['varianty'] = $variantyvDB[$kombinaceId] ?? [];
            $seznam[$key]['skladem'] = $skladem[$kombinaceId] ?? 0;
            $seznam[$key]['stitky'] = $stitkyvDB[$produktId] ?? [];
        }
        return $seznam;
    }
}