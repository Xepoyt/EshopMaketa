<?php

namespace App\Models\ProduktVariantaKombinaceModel;

use App\Models\BaseModel;
class ProduktVariantaKombinaceModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'produkt_varianta_kombinace';
    }
}