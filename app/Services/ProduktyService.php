<?php 

namespace App\Services;

use App\Models\KombinaceModel\KombinaceModel;
use App\Models\ProduktModel\ProduktModel;
use App\Models\ProduktVariantaKombinaceModel\ProduktVariantaKombinaceModel;
use App\Models\ProduktVariantaModel\ProduktVariantaModel;
use App\Models\VariantaModel\VariantaModel;

use Tracy\Debugger;


class ProduktyService
{

    /** @var KombinaceModel */
    public KombinaceModel $kombinaceModel;
    /** @var ProduktModel */
    public ProduktModel $produktModel;
    /** @var ProduktVariantaKombinaceModel */
    public ProduktVariantaKombinaceModel $produktVariantaKombinaceModel;
    /** @var ProduktVariantaModel */
    public ProduktVariantaModel $produktVariantaModel;
    /** @var VariantaModel */
    public VariantaModel $variantaModel;


    public array $produktySkladem = [];
    public array $varianty = [];
    public array $produktVariantaData = []; // produkt_varianta
    public array $kombinace = [];
    public array $produktVariantaKombinaceData = []; // produkt_varianta_kombinace
    public array $fullProduktVariantaKombinaceData = []; // vsechny produkt_varianta_kombinace
    public array $variantaData = [];

    public function __construct(
        KombinaceModel $kombinaceModel,
        ProduktModel $produktModel,
        ProduktVariantaKombinaceModel $produktVariantaKombinaceModel,
        ProduktVariantaModel $produktVariantaModel,
        VariantaModel $variantaModel)
    {
        $this->kombinaceModel = $kombinaceModel;
        $this->produktModel = $produktModel;
        $this->produktVariantaKombinaceModel = $produktVariantaKombinaceModel;
        $this->produktVariantaModel = $produktVariantaModel;
        $this->variantaModel = $variantaModel;
    }


    public function najdiProduktySkladem(): void
    {
        $this->kombinace = $this->kombinaceModel->getKombinaceSkladem();
        $this->variantaData = $this->variantaModel->getPary("id", "nazev");

        $kombinaceIds = array_keys($this->kombinace);

        $this->produktVariantaKombinaceData = [];
        $this->produktVariantaData = [];
        $this->produktySkladem = [];
        $this->fullProduktVariantaKombinaceData = [];

        if (!empty($kombinaceIds)) {

            //produkt_varianta_kombinace
            $produktVariantaKombinaceRows = $this->produktVariantaKombinaceModel->getSeznam("kombinace_id", $kombinaceIds);

            $variantyIds = [];
            foreach ($produktVariantaKombinaceRows as $row) {
                $this->produktVariantaKombinaceData[$row->produkt_varianta_id] = $row->kombinace_id;

                //vytvoření pole všech kombinací pro danou variantu
                $this->fullProduktVariantaKombinaceData[$row->produkt_varianta_id][] = $row->kombinace_id;
                
                //sbírání ID variant pro další dotaz
                $variantyIds[] = $row->produkt_varianta_id;
            }
            
            $variantyIds = array_unique($variantyIds);

            if (!empty($variantyIds)) {
                
                //produkt_varianta
                $this->produktVariantaData = $this->produktVariantaModel->getSeznam("id", $variantyIds);

                //sbírání ID produktů z variant
                $produktyIds = array_unique(array_map(fn($v) => $v->produkt_id, $this->produktVariantaData));

                //produkt
                if (!empty($produktyIds)) {
                    $this->produktySkladem = $this->produktModel->getSeznam("id", $produktyIds);
                }
            }
        }

        
        //// usetreni mnoha sql dotazu: (SQL - tracy...cca 3500ms vs PHP - tracy...cca 3000ms).......mam se zabit nebo????
        //* SQL je pry rychlejsi


        // // $produktVariantaKombinace = $this->produktVariantaKombinaceModel->getZaznamy()->fetchPairs("produkt_varianta_id", "kombinace_id");
        // // $produktVarianta = $this->produktVariantaModel->getZaznamy()->fetchAll();
        // // $produkt = $this->produktModel->getZaznamy()->fetchAll();
        // // $fullProduktVariantaKombinace = $this->produktVariantaKombinaceModel->getZaznamy()->fetchAll();
        
        // // $pvkPomocna = [];
        // // foreach($this->kombinace as $key => $polozka) {
        // //     $pvkPomocna[] = array_filter($produktVariantaKombinace, fn($kombinaceId) => $kombinaceId == $key);
        // // }
        // // $pvkPomocna = array_filter($pvkPomocna);
        // // $this->produktVariantaKombinaceData = [];
        // // foreach($pvkPomocna as $key => $produktVariantaKombinacePolozky){
        // //     foreach($produktVariantaKombinacePolozky as $produktVariantaId => $kombinaceId){
        // //         $this->produktVariantaKombinaceData[$produktVariantaId] = $kombinaceId;
        // //     }
        // // }

        // // $pvPomocna = [];
        // // foreach($this->produktVariantaKombinaceData as $produktVariantaId => $kombinaceId){
        // //     $pvPomocna[] = array_filter($produktVarianta, fn($item) => $item->id == $produktVariantaId);
        // // }
        // // $pvPomocna = array_filter($pvPomocna);
        // // foreach($pvPomocna as $key => $produktVariantaPolozky){
        // //     foreach($produktVariantaPolozky as $key => $produktVariantaPolozka){
        // //         $this->produktVariantaData[$key] = $produktVariantaPolozka;
        // //     }
        // // }


        // // $produktyId = [];
        // // foreach($this->produktVariantaData as $key => $produktVariantaPolozka){
        // //     $produktyId[] = $produktVariantaPolozka->produkt_id;
        // // }
        // // $produktyId = array_unique($produktyId);

        // // $produktySklademPomocna = [];
        // // foreach($produktyId as $key => $produktId){
        // //     $produktySklademPomocna[] = array_filter($produkt, fn($item) => $item->id == $produktId);
        // // }
        // // $produktySklademPomocna = array_filter($produktySklademPomocna);
        // // foreach($produktySklademPomocna as $key => $produktySklademPolozky){
        // //     foreach($produktySklademPolozky as $key => $produktySklademPolozka){
        // //         $this->produktySkladem[$produktySklademPolozka->id] = $produktySklademPolozka;
        // //     }
        // // }


        // // $this->produktySkladem = array_unique($this->produktySkladem);

        // // $fullProduktVariantaKombinace = array_filter($fullProduktVariantaKombinace, fn($item) => array_key_exists($item->kombinace_id, $this->kombinace));
        // // foreach($fullProduktVariantaKombinace as $key => $polozka){
        // //     $this->fullProduktVariantaKombinaceData[$polozka->produkt_varianta_id][] = $polozka->kombinace_id;
        // // }
    }

    public function najdiVarianty(): void
    {
        //* varianty[produktId => [nazevVarianty => [hodnotaVarianty]]] (varianty = [1 => ["Barva" => ["černá", "bílá"], "Velikost" => ["S", "M", "L"]]])
        foreach($this->produktVariantaData as $key => $produktVarianta){
            if(!array_key_exists($produktVarianta->produkt_id, $this->varianty)){
                $this->varianty[$produktVarianta->produkt_id] = [];
            }
            if(!isset($this->variantaData[$produktVarianta->varianta_id])){
                continue;
            }
            if(!array_key_exists($this->variantaData[$produktVarianta->varianta_id], $this->varianty[$produktVarianta->produkt_id])){
                $this->varianty[$produktVarianta->produkt_id][$this->variantaData[$produktVarianta->varianta_id]] = [];
            }
            $this->varianty[$produktVarianta->produkt_id][$this->variantaData[$produktVarianta->varianta_id]][] = $produktVarianta->varianta_hodnota;
        }

        foreach($this->varianty as $produktId => $druhyVariant){
            foreach($druhyVariant as $nazevVarianty => $hodnotaVarianty){
                $this->varianty[$produktId][$nazevVarianty] = array_unique($hodnotaVarianty);
            }
        }
    }

    public function variantyProduktu(int $produktId){
        $vysledneVarianty = [];
        $radky = $this->produktVariantaModel->najitAll("produkt_id", $produktId);
        if(empty($this->variantaData)){
            $this->variantaData = $this->variantaModel->getPary("id", "nazev");
        }
        foreach($radky as $key => $radek){
            if(!isset($this->variantaData[$radek->varianta_id])){
                continue;
            }
            $nazevVarianty = $this->variantaData[$radek->varianta_id];
            if(!array_key_exists($nazevVarianty, $vysledneVarianty)){
                $vysledneVarianty[$nazevVarianty] = [];
            }
            $vysledneVarianty[$nazevVarianty][] = $radek->varianta_hodnota;
        }
        foreach($vysledneVarianty as $nazevVarianty => $hodnotaVarianty){
            $unikatniHodnoty = array_unique($hodnotaVarianty);
            $vysledneVarianty[$nazevVarianty] = array_combine($unikatniHodnoty, $unikatniHodnoty);
        }

        return $vysledneVarianty;
    }

    public function dostupnostKombinace(int $produktId, array $volby): array
    {
        $this->najdiProduktySkladem();
        Debugger::barDump($volby, "Volby v ProduktyService");
        $kombinaceIds = [];
        foreach($volby as $key => $value){
            $hledanaVarianta = intval(str_replace("varianta_", "", $key));
            $produktVarianta0 = array_filter($this->produktVariantaData, fn($item) => $item->produkt_id == $produktId && $item->varianta_id == $hledanaVarianta && strcmp($item->varianta_hodnota, $value) == 0);
            $produktVarianta0 = reset($produktVarianta0);
            if(!$produktVarianta0){
                continue;
            }
            Debugger::barDump($produktVarianta0, "PV0 pro $key - $value");
            $kombinaceIds[] = $this->fullProduktVariantaKombinaceData[$produktVarianta0->id];
        }
        $prunik = empty($kombinaceIds) ? [] : reset($kombinaceIds);
        if(!empty($kombinaceIds)){
            foreach($kombinaceIds as $kombinaceId){
                $prunik = array_intersect($prunik, $kombinaceId);
            }
        }
        Debugger::barDump($prunik, "Prunik kombinaci pro momentalni vybrane varianty");

        if(count($prunik) == 0){
            return ['ks' => 0, 'kombinaceId' => null];
        }
        elseif(count($prunik) == 1){
            $kombinaceId = reset($prunik);
            return ['ks' => $this->kombinace[$kombinaceId] ?? 0, 'kombinaceId' => $kombinaceId];
        }
        return ['ks' => null, 'kombinaceId' => null];
    }

    public function getSklademBezVariant(int $produktId): int
    {
        $produktVarianta0 = $this->produktVariantaModel->najit("produkt_id", $produktId);
        if(!$produktVarianta0){
            return 0;
        }
        $produktVariantaKombinace0 = $this->produktVariantaKombinaceModel->najit("produkt_varianta_id", $produktVarianta0->id);
        if(!$produktVariantaKombinace0){
            return 0;
        }
        $kombinace0 = $this->kombinaceModel->najit("id", $produktVariantaKombinace0->kombinace_id);
        return $kombinace0 ? $kombinace0->kusy : 0;
    }

    //* obsolete, přesunuto do StitkyService
    // // public function najdiStitky(): void
    // // {
    // //     $produktStitekModel = $this->produktStitekModel;
    // //     $stitekModel = $this->stitekModel;

    // //     $produktStitekData = $produktStitekModel->getZaznamyAll();
    // //     $stitekData = $stitekModel->getPary("id", "text");


    // //     foreach($produktStitekData as $key => $produktStitek){
    // //         if(!array_key_exists($produktStitek->produkt_id, $this->stitky)){
    // //             $this->stitky[$produktStitek->produkt_id] = [];
    // //         }
    // //         $this->stitky[$produktStitek->produkt_id][] = $stitekData[$produktStitek->stitek_id];
    // //     }

    // // }

    //* obsolete, přesunuto do ObjednavkaService
    // // public function ulozObjednavku($values): void
    // // {
    // //     $this->database->beginTransaction();

    // //     try{
    // //         Debugger::barDump($values, "Ulozeni objednavky v ProduktyService");
    // //         $objednavka = $this->objednavkaModel->vlozit([
    // //             'email' => $values->email,
    // //             'jmeno' => $values->jmeno,
    // //             'telefon' => $values->telefon,
    // //         ]);
    // //         $objednavkaId = $objednavka->id;

    // //         $sectionK = $this->presenter->getSession()->getSection('kosik');
    // //         $kosik = $sectionK->get('seznam');

    // //         $data = [];
    // //         foreach($kosik as $key => $polozka){
    // //             if($polozka['ks'] == 0){
    // //                 continue;
    // //             }
    // //             $data[] = [
    // //                 'objednavka_id' => $objednavkaId,
    // //                 'kombinace_id' => $polozka['kombinace_id'],
    // //                 'kusy' => $polozka['ks'],
    // //             ];

    // //             $this->kombinaceModel->upravit("id", $polozka['kombinace_id'], [
    // //                 'kusy' => $this->kombinace[$polozka['kombinace_id']] - $polozka['ks'],
    // //             ]);
    // //         }
    // //         if(!empty($data)){
    // //             $this->objednavkaKombinaceModel->vlozit($data);
    // //         }

    // //         $this->database->commit();
    // //         $this->presenter->flashMessage('Objednávka byla úspěšně vytvořena.', 'success');
    // //     }
    // //     catch(\Exception $e){
    // //         $this->database->rollBack();
    // //         $this->presenter->flashMessage('Při vytváření objednávky došlo k chybě. Zkuste to prosím znovu.', 'danger');
    // //     }
    // // }
}