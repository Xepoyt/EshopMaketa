<?php
namespace App\Services;

use App\Models\KombinaceModel\KombinaceModel;
use App\Models\ProduktVariantaKombinaceModel\ProduktVariantaKombinaceModel;
use App\Models\ProduktVariantaModel\ProduktVariantaModel;
use App\Models\VariantaModel\VariantaModel;

use Tracy\Debugger;

class VariantyService
{
    private KombinaceModel $kombinaceModel;
    private ProduktVariantaKombinaceModel $produktVariantaKombinaceModel;
    private ProduktVariantaModel $produktVariantaModel;
    private VariantaModel $variantaModel;

    public function __construct(KombinaceModel $kombinaceModel, ProduktVariantaKombinaceModel $produktVariantaKombinaceModel, ProduktVariantaModel $produktVariantaModel, VariantaModel $variantaModel)
    {
        $this->kombinaceModel = $kombinaceModel;
        $this->produktVariantaKombinaceModel = $produktVariantaKombinaceModel;
        $this->produktVariantaModel = $produktVariantaModel;
        $this->variantaModel = $variantaModel;
    }

    
    public function variantyProduktu(int $produktId){
        $vysledneVarianty = [];
        $radky = $this->produktVariantaModel->najitAll("produkt_id", $produktId);
        
        foreach($radky as $key => $radek){
            $varianta = $radek->varianta;
            if(!$varianta){
                continue;
            }
            $nazevVarianty = $varianta->nazev;
            $variantaId = $varianta->id;
            $variantaHodnota = $radek->varianta_hodnota;

            $vysledneVarianty[$variantaId]['nazev'] = $nazevVarianty;
            $vysledneVarianty[$variantaId]['hodnoty'][$variantaHodnota] = $variantaHodnota;
        }

        return $vysledneVarianty;
    }

    public function variantyViceProduktu(array $produktIds): array
    {
        if(empty($produktIds)){
            return [];
        }

        $radky = $this->produktVariantaModel->najitAll("produkt_id", $produktIds);
        $vysledneVarianty = [];
        foreach($radky as $radek){
            $produktId = $radek->produkt_id;
            $varianta = $radek->varianta;
            if(!$varianta){
                continue;
            }
            $nazevVarianty = $varianta->nazev;
            $hodnotaVarianty = $radek->varianta_hodnota;

            if(!isset($vysledneVarianty[$produktId][$nazevVarianty]) || !in_array($hodnotaVarianty, $vysledneVarianty[$produktId][$nazevVarianty])){
                $vysledneVarianty[$produktId][$nazevVarianty][] = $hodnotaVarianty;
            }
        }
        return $vysledneVarianty;
    }

    public function dostupnostKombinace(int $produktId, array $volby): array
    {
        Debugger::barDump($volby, "Volby v ProduktyService");
        $kombinaceIds = [];
        foreach($volby as $key => $value){
            $hledanaVarianta = intval(str_replace("varianta_", "", $key));
            $produktVarianta0 = $this->produktVariantaModel->najitPodle([
                "produkt_id" => $produktId,
                "varianta_id" => $hledanaVarianta,
                "varianta_hodnota" => $value,
            ]);
            if(!$produktVarianta0){
                return ['ks' => 0, 'kombinaceId' => null];
            }

            $vazby = $this->produktVariantaKombinaceModel->najitAll("produkt_varianta_id", $produktVarianta0->id);
            $idsProVariantu = array_map(fn($vazba) => $vazba->kombinace_id, $vazby);
            Debugger::barDump($produktVarianta0, "PV0 pro $key - $value");
            $kombinaceIds[] = $idsProVariantu;
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
            $kombinacevDB = $this->kombinaceModel->najit("id", $kombinaceId);
            return ['ks' => $kombinacevDB ? $kombinacevDB->kusy : 0, 'kombinaceId' => $kombinaceId];
        }
        return ['ks' => null, 'kombinaceId' => null];
    }
}