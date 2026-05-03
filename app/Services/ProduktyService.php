<?php 

namespace App\Services;

use App\Models\KombinaceModel\KombinaceModel;
use App\Models\ProduktModel\ProduktModel;
use App\Models\ProduktVariantaKombinaceModel\ProduktVariantaKombinaceModel;
use App\Models\ProduktVariantaModel\ProduktVariantaModel;
use App\Models\VariantaModel\VariantaModel;
use App\Services\StitkyService;
use App\Services\VariantyService;

use Tracy\Debugger;


class ProduktyService
{

    /** @var KombinaceModel */
    public KombinaceModel $kombinaceModel;
    /** @var ProduktModel */
    public ProduktModel $produktModel;
    /** @var ProduktVariantaKombinaceModel */
    public ProduktVariantaKombinaceModel $produktVariantaKombinaceModel;
    /** @var ProduktVariantaModel */
    public ProduktVariantaModel $produktVariantaModel;
    /** @var VariantaModel */
    public VariantaModel $variantaModel;
    /** @var StitkyService */
    public StitkyService $stitkyService;
    /** @var VariantyService */
    public VariantyService $variantyService;


    public function __construct(
        KombinaceModel $kombinaceModel,
        ProduktModel $produktModel,
        ProduktVariantaKombinaceModel $produktVariantaKombinaceModel,
        ProduktVariantaModel $produktVariantaModel,
        VariantaModel $variantaModel,
        StitkyService $stitkyService,
        VariantyService $variantyService)
    {
        $this->kombinaceModel = $kombinaceModel;
        $this->produktModel = $produktModel;
        $this->produktVariantaKombinaceModel = $produktVariantaKombinaceModel;
        $this->produktVariantaModel = $produktVariantaModel;
        $this->variantaModel = $variantaModel;
        $this->stitkyService = $stitkyService;
        $this->variantyService = $variantyService;
    }


    public function getSklademBezVariant(int $produktId): int
    {
        $produktVarianta0 = $this->produktVariantaModel->najit("produkt_id", $produktId);
        if(!$produktVarianta0){
            return 0;
        }
        $produktVariantaKombinace0 = $this->produktVariantaKombinaceModel->najit("produkt_varianta_id", $produktVarianta0->id);
        if(!$produktVariantaKombinace0){
            return 0;
        }
        $kombinace0 = $this->kombinaceModel->najit("id", $produktVariantaKombinace0->kombinace_id);
        return $kombinace0 ? $kombinace0->kusy : 0;
    }

    public function getStrankovaneProdukty(int $limit, int $offset): array
    {
        $produktyvDB = $this->produktModel->getSeznamLimit($limit, $offset);
        if(empty($produktyvDB)){
            return [];
        }

        $produktIds = array_keys($produktyvDB);

        $stitkyProProdukty = $this->stitkyService->najdiStitkyProProdukty($produktIds);
        $variantyProProdukty = $this->variantyService->variantyViceProduktu($produktIds);

        $vysledek = [];
        foreach($produktyvDB as $produktId => $produkt){
            $vysledek[$produktId] = [
                'id' => $produkt->id,
                'nazev' => $produkt->nazev,
                'cena100' => $produkt->cena100,
                'varianty' => $variantyProProdukty[$produktId] ?? [],
                'stitky' => $stitkyProProdukty[$produktId] ?? [],
            ];
        }

        return $vysledek;
    }

    public function getPocetProduktu(): int
    {
        return $this->produktModel->getPocetZaznamu();
    }

    //* obsolete, přesunuto do StitkyService
    // // public function najdiStitky(): void
    // // {
    // //     $produktStitekModel = $this->produktStitekModel;
    // //     $stitekModel = $this->stitekModel;

    // //     $produktStitekData = $produktStitekModel->getZaznamyAll();
    // //     $stitekData = $stitekModel->getPary("id", "text");


    // //     foreach($produktStitekData as $key => $produktStitek){
    // //         if(!array_key_exists($produktStitek->produkt_id, $this->stitky)){
    // //             $this->stitky[$produktStitek->produkt_id] = [];
    // //         }
    // //         $this->stitky[$produktStitek->produkt_id][] = $stitekData[$produktStitek->stitek_id];
    // //     }

    // // }

    //* obsolete, přesunuto do ObjednavkaService
    // // public function ulozObjednavku($values): void
    // // {
    // //     $this->database->beginTransaction();

    // //     try{
    // //         Debugger::barDump($values, "Ulozeni objednavky v ProduktyService");
    // //         $objednavka = $this->objednavkaModel->vlozit([
    // //             'email' => $values->email,
    // //             'jmeno' => $values->jmeno,
    // //             'telefon' => $values->telefon,
    // //         ]);
    // //         $objednavkaId = $objednavka->id;

    // //         $sectionK = $this->presenter->getSession()->getSection('kosik');
    // //         $kosik = $sectionK->get('seznam');

    // //         $data = [];
    // //         foreach($kosik as $key => $polozka){
    // //             if($polozka['ks'] == 0){
    // //                 continue;
    // //             }
    // //             $data[] = [
    // //                 'objednavka_id' => $objednavkaId,
    // //                 'kombinace_id' => $polozka['kombinace_id'],
    // //                 'kusy' => $polozka['ks'],
    // //             ];

    // //             $this->kombinaceModel->upravit("id", $polozka['kombinace_id'], [
    // //                 'kusy' => $this->kombinace[$polozka['kombinace_id']] - $polozka['ks'],
    // //             ]);
    // //         }
    // //         if(!empty($data)){
    // //             $this->objednavkaKombinaceModel->vlozit($data);
    // //         }

    // //         $this->database->commit();
    // //         $this->presenter->flashMessage('Objednávka byla úspěšně vytvořena.', 'success');
    // //     }
    // //     catch(\Exception $e){
    // //         $this->database->rollBack();
    // //         $this->presenter->flashMessage('Při vytváření objednávky došlo k chybě. Zkuste to prosím znovu.', 'danger');
    // //     }
    // // }
}