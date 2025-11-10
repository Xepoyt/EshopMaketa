<?php

namespace App\Models\ProduktModel;

use App\Models\BaseModel;
class ProduktModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'produkt';
    }
}