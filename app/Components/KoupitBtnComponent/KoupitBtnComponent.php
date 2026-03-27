<?php 

namespace App\Components\KoupitBtnComponent;

use App\Components\BaseComponent;
use App\Services\ProduktyService;
use Nette\Database\Table\ActiveRow;

class KoupitBtnComponent extends BaseComponent
{
    public ActiveRow $produkt;
    public ProduktyService $produktyService;
    public int $ks = 0;

    public function __construct()
    {
        $this->parameters = ['ks', 'produkt'];
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderButton($produkt): void
    {
        $this->produktyService = $this->presenter->produktyService;
        $this->produkt = $produkt;

        $this->produktyService->najdiProduktySkladem();


        $produktVarianta0 = $this->produktyService->produktVariantaModel->najit("produkt_id", $produkt->id);

        $produktVariantaKombinace0 = $this->produktyService->produktVariantaKombinaceData[$produktVarianta0->id];

        $this->ks = $this->produktyService->kombinace[$produktVariantaKombinace0] ?? 0;

        $this->render();
    }

    public function handleKoupit(int $id): void
    {
        $this->produktyService = $this->presenter->produktyService;

        $this->produktyService->najdiProduktySkladem();

        $produkt = $this->produktyService->produktModel->najit("id", $id);

        $produktVarianta0 = $this->produktyService->produktVariantaModel->najit("produkt_id", $produkt->id);

        $produktVariantaKombinace0 = $this->produktyService->produktVariantaKombinaceData[$produktVarianta0->id];
        $produktVariantaKombinace0 = reset($produktVariantaKombinace0);

        //* kosik bude obsahovat pole poli [ActiveRow produkt, int kombinace_id, int ks]
        $section = $this->presenter->session->getSection("kosik");
        if($section->get("seznam") === null) {
            $section->set("seznam", []);
        }

        $seznam = $section->get("seznam");

        $polozka = array_filter($seznam, fn($item) => $item['kombinace_id'] == $produktVariantaKombinace0);
        $polozka = reset($polozka);

        $max = $this->produktyService->kombinace[$produktVariantaKombinace0];

        if(!$polozka){
            $section->set("seznam", array_merge($section->get("seznam"), [['produkt_id' => $produkt->id, 'produkt_nazev' => $produkt->nazev, 'produkt_cena' => $produkt->cena100 / 100, 'kombinace_id' => $produktVariantaKombinace0, 'ks' => 1]]));
        }
        else{
            $novaKs = $polozka['ks'] + 1;
            if($novaKs > $max){
                $novaKs = $max;
            }
            $novaPolozka = ['produkt_id' => $produkt->id, 'produkt_nazev' => $produkt->nazev, 'produkt_cena' => $produkt->cena100 / 100, 'kombinace_id' => $produktVariantaKombinace0, 'ks' => $novaKs];

            //odstranime starou polozku
            $seznamBezStare = array_filter($seznam, fn($item) => $item['kombinace_id'] != $produktVariantaKombinace0);
            //pridame novou polozku
            $seznamBezStare[] = $novaPolozka;

            $section->set("seznam", $seznamBezStare);
        }
        
        

        if ($this->presenter->isAjax()) {
            $this->presenter["kosikNahled"]->redrawControl();
        } else {
            $this->presenter->redirect('this');
        }
    }

}