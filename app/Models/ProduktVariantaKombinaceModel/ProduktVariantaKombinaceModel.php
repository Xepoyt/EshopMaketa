<?php

namespace App\Models\ProduktVariantaKombinaceModel;

use App\Models\BaseModel;
class ProduktVariantaKombinaceModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'produkt_varianta_kombinace';
    }
    public function najdiVariantyKombinace(array $kombinaceIds): array
    {
        if(empty($kombinaceIds)){
            return [];
        }
        $radky = $this->najitAll('kombinace_id', $kombinaceIds);
        $vysledek = [];

        foreach($radky as $radek){
            $varianta = $radek->produkt_varianta;
            $kombinaceId = $radek->kombinace_id;
            $nazev = $varianta->varianta->nazev;
            $hodnota = $varianta->varianta_hodnota;
            $vysledek[$kombinaceId][$nazev][] = $hodnota;
        }
        return $vysledek;
    }
}