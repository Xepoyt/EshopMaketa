<?php

namespace App\Models\KombinaceModel;

use App\Models\BaseModel;

class KombinaceModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'kombinace';
    }
}