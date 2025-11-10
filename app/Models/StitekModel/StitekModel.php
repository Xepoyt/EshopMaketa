<?php

namespace App\Models\StitekModel;

use App\Models\BaseModel;
class StitekModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'stitek';
    }
}