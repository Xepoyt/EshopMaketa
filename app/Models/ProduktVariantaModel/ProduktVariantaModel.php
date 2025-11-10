<?php

namespace App\Models\ProduktVariantaModel;

use App\Models\BaseModel;
class ProduktVariantaModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'produkt_varianta';
    }
}