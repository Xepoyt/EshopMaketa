<?php

namespace App\Components\ProduktyComponent;

use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;
use App\Services\MenaService;
use App\Components\StitekComponent\StitekComponent;
use App\Components\KoupitModalComponent\KoupitModalComponent;

class ProduktyComponent extends BaseComponent
{
    public array $produktySkladem = [];
    public array $varianty = [];
    public array $stitky = [];
    public array $pv = []; // produkt_varianta
    public array $kombinace = [];
    public array $pvk = []; // produkt_varianta_kombinace
    public MenaService $menaService;
    public int $koupitModal = 0;

    public function __construct()
    {
        $this->parameters = ['produktySkladem', 'menaService', 'varianty', 'stitky', 'koupitModal'];
        $this->menaService = new MenaService();
    }

    public function render(): void
    {
        $this->najdiProduktySkladem();
        $this->najdiVarianty();
        $this->najdiStitky();
        parent::render();
    }

    private function najdiProduktySkladem(): void
    {
        $kombinaceModel = $this->getPresenter()->kombinace;
        $produktVariantaKombinaceModel = $this->getPresenter()->produktVariantaKombinace;
        $produktVariantaModel = $this->getPresenter()->produktVarianta;
        $produktModel = $this->getPresenter()->produkt;

        $this->kombinace = $kombinaceModel->getZaznamy()->where("kusy > 0")->fetchPairs("id", "kusy");
        $produktVariantaKombinace = $produktVariantaKombinaceModel->getZaznamy()->fetchPairs("produkt_varianta_id", "kombinace_id");
        $produktVarianta = $produktVariantaModel->getZaznamy()->fetchAll();
        $produkt = $produktModel->getZaznamy()->fetchAll();

        /*
        //    $this->pvk = [];
        //    foreach($kombinace as $key => $polozka) {
        //        $this->pvk[] = $produktVariantaKombinaceModel->getZaznamy()
        //            ->where("kombinace_id", $polozka->id)
        //            ->fetchPairs("produkt_varianta_id", "kombinace_id");
        //    }
        //    Debugger::barDump($this->pvk);
        //    $produktyId = [];
        //    foreach($this->pvk as $key => $produktVariantaKombinacePolozky){
        //        foreach($produktVariantaKombinacePolozky as $produktVariantaId => $kombinaceId){
        //            $this->pv[] = $produktVariantaModel->getZaznamy()
        //                ->where("id", $produktVariantaId)
        //                ->fetchAll();
        //        }
        //    }
        //    foreach($this->pv as $key => $produktVariantaPolozky){
        //        foreach($produktVariantaPolozky as $produktVariantaPolozka){
        //            $produktyId[] = $produktVariantaPolozka->produkt_id;
        //        }
        //    }
        //    Debugger::barDump($this->pv);
        //    $produktyId = array_unique($produktyId);
        //    Debugger::barDump($produktyId);

        //    foreach($produktyId as $key => $produktId){
        //        $this->produktySkladem[] = $produktModel->getZaznamy()
        //            ->where("id", $produktId)
        //            ->fetch();
        //    }
        */
        //* usetreni mnoha sql dotazu: (SQL - tracy...cca 3500ms vs PHP - tracy...cca 3000ms).......mam se zabit nebo????

        
        $pvkPomocna = [];
        foreach($this->kombinace as $key => $polozka) {
            $pvkPomocna[] = array_filter($produktVariantaKombinace, fn($kombinaceId) => $kombinaceId == $key);
        }
        $pvkPomocna = array_filter($pvkPomocna);

        $this->pvk = [];
        foreach($pvkPomocna as $key => $produktVariantaKombinacePolozky){
            foreach($produktVariantaKombinacePolozky as $produktVariantaId => $kombinaceId){
                $this->pvk[$produktVariantaId] = $kombinaceId;
            }
        }
        Debugger::barDump($this->pvk);

        $pvPomocna = [];
        foreach($this->pvk as $produktVariantaId => $kombinaceId){
            $pvPomocna[] = array_filter($produktVarianta, fn($item) => $item->id == $produktVariantaId);
        }
        $pvPomocna = array_filter($pvPomocna);
        foreach($pvPomocna as $key => $produktVariantaPolozky){
            foreach($produktVariantaPolozky as $key => $produktVariantaPolozka){
                $this->pv[$key] = $produktVariantaPolozka;
            }
        }

        Debugger::barDump($this->pv);

        $produktyId = [];
        foreach($this->pv as $key => $produktVariantaPolozka){
            $produktyId[] = $produktVariantaPolozka->produkt_id;
        }
        $produktyId = array_unique($produktyId);
        Debugger::barDump($produktyId);

        $produktySklademPomocna = [];
        foreach($produktyId as $key => $produktId){
            $produktySklademPomocna[] = array_filter($produkt, fn($item) => $item->id == $produktId);
        }
        $produktySklademPomocna = array_filter($produktySklademPomocna);
        foreach($produktySklademPomocna as $key => $produktySklademPolozky){
            foreach($produktySklademPolozky as $key => $produktySklademPolozka){
                $this->produktySkladem[$produktySklademPolozka->id] = $produktySklademPolozka;
            }
        }

        Debugger::barDump($this->produktySkladem);
        $this->produktySkladem = array_unique($this->produktySkladem);
    }

    private function najdiVarianty(): void
    {
        $variantaModel = $this->getPresenter()->varianta;
        $v = $variantaModel->getZaznamy()->fetchPairs("id", "nazev");
        //* varianty[produktId => [nazevVarianty => [hodnotaVarianty]]] (varianty = [1 => ["Barva" => ["černá", "bílá"], "Velikost" => ["S", "M", "L"]]])
        foreach($this->pv as $key => $produktVarianta){
            if(!array_key_exists($produktVarianta->produkt_id, $this->varianty)){
                $this->varianty[$produktVarianta->produkt_id] = [];
            }
            if(!isset($v[$produktVarianta->varianta_id])){
                continue;
            }
            if(!array_key_exists($v[$produktVarianta->varianta_id], $this->varianty[$produktVarianta->produkt_id])){
                $this->varianty[$produktVarianta->produkt_id][$v[$produktVarianta->varianta_id]] = [];
            }
            $this->varianty[$produktVarianta->produkt_id][$v[$produktVarianta->varianta_id]][] = $produktVarianta->varianta_hodnota;
        }

        foreach($this->varianty as $produktId => $druhyVariant){
            foreach($druhyVariant as $nazevVarianty => $hodnotaVarianty){
                $this->varianty[$produktId][$nazevVarianty] = array_unique($hodnotaVarianty);
            }
        }

        Debugger::barDump($this->varianty);
    }

    private function najdiStitky(): void
    {
        $produktStitekModel = $this->getPresenter()->produktStitek;
        $stitekModel = $this->getPresenter()->stitek;

        $ps = $produktStitekModel->getZaznamy()->fetchAll();
        $s = $stitekModel->getZaznamy()->fetchPairs("id", "text");

        Debugger::barDump($s, 'Stitky');
        Debugger::barDump($ps, 'Produkt Stitky');

        foreach($ps as $key => $produktStitek){
            if(!array_key_exists($produktStitek->produkt_id, $this->stitky)){
                $this->stitky[$produktStitek->produkt_id] = [];
            }
            $this->stitky[$produktStitek->produkt_id][] = $s[$produktStitek->stitek_id];
        }

        Debugger::barDump($this->stitky);
    }

    public function handleKoupit(int $id): void
    {
        //* kosik bude obsahovat pole poli [ActiveRow produkt, int kombinace_id]
        $section = $this->getPresenter()->session->getSection("kosik");
        $this->najdiProduktySkladem();

        if($section->get("seznam") === null) {
            Debugger::barDump('Initializing cart session - produkty component');
            $section->set("seznam", []);
        }
        //TODO: kupovaci modal

        $produkt = array_filter($this->produktySkladem, fn($item) => $item->id == $id);
        $produkt = reset($produkt);

        $pv0 = array_filter($this->pv, fn($item) => $item->produkt_id == $id);
        $pv0 = reset($pv0);
        Debugger::barDump($pv0, 'PV0');

        if(!isset($pv0->varianta_id)){
            // produkt nema zadny druh variant
            Debugger::barDump($this->kombinace, 'Kombinace');
            $pvk0 = array_filter($this->pvk, fn($produktVariantaId) => $produktVariantaId == $pv0->id, ARRAY_FILTER_USE_KEY);
            $pvk0 = reset($pvk0);
            Debugger::barDump("k0 = " . $pvk0, 'PVK0');
            $section->set("seznam", array_merge($section->get("seznam"), [['produkt_id' => $produkt->id, 'produkt_nazev' => $produkt->nazev, 'produkt_cena' => $produkt->cena100 / 100, 'kombinace_id' => $pvk0]]));
            $this->koupitModal = 0;
        } else {
            $this->koupitModal = $produkt->id;
        }
        /*
        Debugger::barDump($this->produktySkladem);
        Debugger::barDump($this->produktySkladem[$id]);
        $section->set("seznam", array_merge($section->get("seznam"), [$this->produktySkladem[$id]]));
        */

        if ($this->getPresenter()->isAjax()) {
            Debugger::barDump($section->get("seznam"), 'After koupit');

            $this->presenter->getComponent('kosikNahled')->redrawControl(); //!neni realne chyba
            Debugger::barDump($this->koupitModal, 'koupitModal po handle');
        } else {
            $this->getPresenter()->redirect('this');
        }
    }

    public function createComponentStitekComponent(): StitekComponent
    {
        return new StitekComponent();
    }
}