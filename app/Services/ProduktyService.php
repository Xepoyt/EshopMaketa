<?php 

namespace App\Services;

use App\Models\KombinaceModel\KombinaceModel;
use App\Models\ObjednavkaKombinaceModel\ObjednavkaKombinaceModel;
use App\Models\ObjednavkaModel\ObjednavkaModel;
use App\Models\ProduktModel\ProduktModel;
use App\Models\ProduktStitekModel\ProduktStitekModel;
use App\Models\ProduktVariantaKombinaceModel\ProduktVariantaKombinaceModel;
use App\Models\ProduktVariantaModel\ProduktVariantaModel;
use App\Models\StitekModel\StitekModel;
use App\Models\VariantaModel\VariantaModel;

class ProduktyService
{
    /** @var KombinaceModel */
    public KombinaceModel $kombinaceModel;
    /** @var ObjednavkaKombinaceModel */
    public ObjednavkaKombinaceModel $objednavkaKombinaceModel;
    /** @var ObjednavkaModel */
    public ObjednavkaModel $objednavkaModel;
    /** @var ProduktModel */
    public ProduktModel $produktModel;
    /** @var ProduktStitekModel */
    public ProduktStitekModel $produktStitekModel;
    /** @var ProduktVariantaKombinaceModel */
    public ProduktVariantaKombinaceModel $produktVariantaKombinaceModel;
    /** @var ProduktVariantaModel */
    public ProduktVariantaModel $produktVariantaModel;
    /** @var StitekModel */
    public StitekModel $stitekModel;
    /** @var VariantaModel */
    public VariantaModel $variantaModel;


    public array $produktySkladem = [];
    public array $varianty = [];
    public array $stitky = [];
    public array $pv = []; // produkt_varianta
    public array $kombinace = [];
    public array $pvk = []; // produkt_varianta_kombinace
    public array $fullPvk = []; // vsechny produkt_varianta_kombinace
    public array $v = [];

    private $p;

    public function __construct(
        KombinaceModel $kombinaceModel,
        ObjednavkaKombinaceModel $objednavkaKombinaceModel,
        ObjednavkaModel $objednavkaModel,
        ProduktModel $produktModel,
        ProduktStitekModel $produktStitekModel,
        ProduktVariantaKombinaceModel $produktVariantaKombinaceModel,
        ProduktVariantaModel $produktVariantaModel,
        StitekModel $stitekModel,
        VariantaModel $variantaModel)
    {
        $this->kombinaceModel = $kombinaceModel;
        $this->objednavkaKombinaceModel = $objednavkaKombinaceModel;
        $this->objednavkaModel = $objednavkaModel;
        $this->produktModel = $produktModel;
        $this->produktStitekModel = $produktStitekModel;
        $this->produktVariantaKombinaceModel = $produktVariantaKombinaceModel;
        $this->produktVariantaModel = $produktVariantaModel;
        $this->stitekModel = $stitekModel;
        $this->variantaModel = $variantaModel;
    }

    public function setPresenter($p): void
    {
        $this->p = $p;
    }

    public function najdiProduktySkladem(): void
    {
        $this->kombinace = $this->kombinaceModel->getZaznamy()->where("kusy > 0")->fetchPairs("id", "kusy");
        $produktVariantaKombinace = $this->produktVariantaKombinaceModel->getZaznamy()->fetchPairs("produkt_varianta_id", "kombinace_id");
        $produktVarianta = $this->produktVariantaModel->getZaznamy()->fetchAll();
        $produkt = $this->produktModel->getZaznamy()->fetchAll();
        $this->v = $this->variantaModel->getZaznamy()->fetchPairs("id", "nazev");

        $fullProduktVariantaKombinace = $this->produktVariantaKombinaceModel->getZaznamy()->fetchAll();

        /*
        //    $this->pvk = [];
        //    foreach($kombinace as $key => $polozka) {
        //        $this->pvk[] = $produktVariantaKombinaceModel->getZaznamy()
        //            ->where("kombinace_id", $polozka->id)
        //            ->fetchPairs("produkt_varianta_id", "kombinace_id");
        //    }
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
        //    $produktyId = array_unique($produktyId);

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


        $produktyId = [];
        foreach($this->pv as $key => $produktVariantaPolozka){
            $produktyId[] = $produktVariantaPolozka->produkt_id;
        }
        $produktyId = array_unique($produktyId);

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


        $this->produktySkladem = array_unique($this->produktySkladem);

        $fullProduktVariantaKombinace = array_filter($fullProduktVariantaKombinace, fn($item) => array_key_exists($item->kombinace_id, $this->kombinace));
        foreach($fullProduktVariantaKombinace as $key => $polozka){
            $this->fullPvk[$polozka->produkt_varianta_id][] = $polozka->kombinace_id;
        }
    }
    public function najdiVarianty(): void
    {
        //* varianty[produktId => [nazevVarianty => [hodnotaVarianty]]] (varianty = [1 => ["Barva" => ["černá", "bílá"], "Velikost" => ["S", "M", "L"]]])
        foreach($this->pv as $key => $produktVarianta){
            if(!array_key_exists($produktVarianta->produkt_id, $this->varianty)){
                $this->varianty[$produktVarianta->produkt_id] = [];
            }
            if(!isset($this->v[$produktVarianta->varianta_id])){
                continue;
            }
            if(!array_key_exists($this->v[$produktVarianta->varianta_id], $this->varianty[$produktVarianta->produkt_id])){
                $this->varianty[$produktVarianta->produkt_id][$this->v[$produktVarianta->varianta_id]] = [];
            }
            $this->varianty[$produktVarianta->produkt_id][$this->v[$produktVarianta->varianta_id]][] = $produktVarianta->varianta_hodnota;
        }

        foreach($this->varianty as $produktId => $druhyVariant){
            foreach($druhyVariant as $nazevVarianty => $hodnotaVarianty){
                $this->varianty[$produktId][$nazevVarianty] = array_unique($hodnotaVarianty);
            }
        }
    }
    public function najdiStitky(): void
    {
        $produktStitekModel = $this->produktStitekModel;
        $stitekModel = $this->stitekModel;

        $ps = $produktStitekModel->getZaznamy()->fetchAll();
        $s = $stitekModel->getZaznamy()->fetchPairs("id", "text");


        foreach($ps as $key => $produktStitek){
            if(!array_key_exists($produktStitek->produkt_id, $this->stitky)){
                $this->stitky[$produktStitek->produkt_id] = [];
            }
            $this->stitky[$produktStitek->produkt_id][] = $s[$produktStitek->stitek_id];
        }

    }
}