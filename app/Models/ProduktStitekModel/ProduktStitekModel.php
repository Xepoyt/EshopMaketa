<?php

namespace App\Models\ProduktStitekModel;

use App\Models\BaseModel;
class ProduktStitekModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'produkt_stitek';
    }
}