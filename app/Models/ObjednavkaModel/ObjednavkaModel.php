<?php

namespace App\Models\ObjednavkaModel;

use App\Models\BaseModel;
class ObjednavkaModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'objednavka';
    }
}