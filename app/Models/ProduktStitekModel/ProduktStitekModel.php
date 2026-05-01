<?php

namespace App\Models\ProduktStitekModel;

use App\Models\BaseModel;
class ProduktStitekModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'produkt_stitek';
    }

    public function najdiStitkyProProdukty(array $produktIds): array
    {
        if(empty($produktIds)){
            return [];
        }
        $radky = $this->najitAll('produkt_id', $produktIds);
        $vysledek = [];

        foreach($radky as $radek){
            $stitek = $radek->stitek;
            $produktId = $radek->produkt_id;
            $nazev = $stitek->text;
            $vysledek[$produktId][] = $nazev;
        }
        return $vysledek;
    }
}