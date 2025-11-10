<?php

namespace App\Models\ObjednavkaKombinaceModel;

use App\Models\BaseModel;
class ObjednavkaKombinaceModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'objednavka_kombinace';
    }
}