<?php

namespace App\Services;

use App\Models\ProduktStitekModel\ProduktStitekModel;
use App\Models\StitekModel\StitekModel;

class StitkyService{
    /** @var ProduktStitekModel */
    private $produktStitekModel;
    /** @var StitekModel */
    private $stitekModel;

    public array $stitky = [];

    public function __construct(
        ProduktStitekModel $produktStitekModel,
        StitekModel $stitekModel
    ) {
        $this->produktStitekModel = $produktStitekModel;
        $this->stitekModel = $stitekModel;
    }

    public function najdiStitky(): void
    {
        $produktStitekModel = $this->produktStitekModel;
        $stitekModel = $this->stitekModel;

        $produktStitekData = $produktStitekModel->getZaznamyAll();
        $stitekData = $stitekModel->getPary("id", "text");


        foreach($produktStitekData as $key => $produktStitek){
            if(!array_key_exists($produktStitek->produkt_id, $this->stitky)){
                $this->stitky[$produktStitek->produkt_id] = [];
            }
            $this->stitky[$produktStitek->produkt_id][] = $stitekData[$produktStitek->stitek_id];
        }

    }
}