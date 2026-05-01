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
            $kombinaceId = $radek->kombinace_id;
            $produktVarianta = $radek->produkt_varianta;
            $varianta = $produktVarianta->varianta;
            if(!$varianta){
                continue;
            }
            $nazev = $varianta->nazev;
            $hodnota = $produktVarianta->varianta_hodnota;
            $vysledek[$kombinaceId][$nazev][] = $hodnota;
        }
        return $vysledek;
    }
}