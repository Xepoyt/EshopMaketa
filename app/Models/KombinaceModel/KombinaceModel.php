<?php

namespace App\Models\KombinaceModel;

use App\Models\BaseModel;

class KombinaceModel extends BaseModel
{
    public function getTableName(): string
    {
        return 'kombinace';
    }

    public function getKombinaceSkladem(): array
    {
        return $this->getZaznamy()->where("kusy > 0")->fetchPairs("id", "kusy");
    }
}