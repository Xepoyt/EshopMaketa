<?php

namespace App\Models\VariantaModel;

use App\Models\BaseModel;
class VariantaModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'varianta';
    }
}