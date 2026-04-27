<?php

namespace App\Services;

use App\Models\KombinaceModel\KombinaceModel;
use App\Models\ObjednavkaKombinaceModel\ObjednavkaKombinaceModel;
use App\Models\ObjednavkaModel\ObjednavkaModel;
use App\Services\KosikService;
use Tracy\Debugger;
use Nette\Database\Explorer;

class ObjednavkaService{
    /** @var Explorer */
    private $database;
    /** @var ObjednavkaModel */
    private $objednavkaModel;
    /** @var ObjednavkaKombinaceModel */
    private $objednavkaKombinaceModel;
    /** @var KombinaceModel */
    private $kombinaceModel;
    /** @var KosikService */
    private $kosikService;

    public array $kombinace = [];

    public function __construct(
        Explorer $database,
        ObjednavkaModel $objednavkaModel,
        ObjednavkaKombinaceModel $objednavkaKombinaceModel,
        KombinaceModel $kombinaceModel,
        KosikService $kosikService
    ) {
        $this->database = $database;
        $this->objednavkaModel = $objednavkaModel;
        $this->objednavkaKombinaceModel = $objednavkaKombinaceModel;
        $this->kombinaceModel = $kombinaceModel;
        $this->kombinace = $this->kombinaceModel->getKombinaceSkladem();
        $this->kosikService = $kosikService;
    }

    public function ulozObjednavku($values): void
    {
        $this->database->beginTransaction();

        try{
            Debugger::barDump($values, "Ulozeni objednavky v ProduktyService");
            $objednavka = $this->objednavkaModel->vlozit([
                'email' => $values->email,
                'jmeno' => $values->jmeno,
                'telefon' => $values->telefon,
            ]);

            $this->zpracujObjednavku($objednavka->id);

            $this->database->commit();
        }
        catch(\Exception $e){
            $this->database->rollBack();
            throw $e;
        }
    }

    private function zpracujObjednavku($objednavkaId): void
    {
        $kosik = $this->kosikService->getSeznam();

        $data = [];
        foreach($kosik as $key => $polozka){
            if($polozka['ks'] == 0){
                continue;
            }
            $data[] = [
                'objednavka_id' => $objednavkaId,
                'kombinace_id' => $polozka['kombinace_id'],
                'kusy' => $polozka['ks'],
            ];

            $this->kombinaceModel->upravit("id", $polozka['kombinace_id'], [
                'kusy' => $this->kombinace[$polozka['kombinace_id']] - $polozka['ks'],
            ]);
        }
        if(!empty($data)){
            $this->objednavkaKombinaceModel->vlozit($data);
        }
    }
}