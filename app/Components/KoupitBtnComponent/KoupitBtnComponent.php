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


        $pv0 = array_filter($this->produktyService->pv, fn($item) => $item->produkt_id == $this->produkt->id);
        $pv0 = reset($pv0);

        $pvk0 = array_filter($this->produktyService->pvk, fn($produktVariantaId) => $produktVariantaId == $pv0->id, ARRAY_FILTER_USE_KEY);
        $pvk0 = reset($pvk0);

        $this->ks = $this->produktyService->kombinace[$pvk0] ?? 0;

        $this->render();
    }

    public function handleKoupit(int $id): void
    {
        $this->produktyService = $this->presenter->produktyService;

        $this->produktyService->najdiProduktySkladem();

        $produkt = array_filter($this->produktyService->produktySkladem, fn($item) => $item->id == $id);
        $produkt = reset($produkt);

        $pv0 = array_filter($this->produktyService->pv, fn($item) => $item->produkt_id == $id);
        $pv0 = reset($pv0);

        $pvk0 = array_filter($this->produktyService->pvk, fn($produktVariantaId) => $produktVariantaId == $pv0->id, ARRAY_FILTER_USE_KEY);
        $pvk0 = reset($pvk0);

        //* kosik bude obsahovat pole poli [ActiveRow produkt, int kombinace_id, int ks]
        $section = $this->presenter->session->getSection("kosik");
        if($section->get("seznam") === null) {
            $section->set("seznam", []);
        }

        $seznam = $section->get("seznam");

        $polozka = array_filter($seznam, fn($item) => $item['kombinace_id'] == $pvk0);
        $polozka = reset($polozka);

        if(!$polozka){
            $section->set("seznam", array_merge($section->get("seznam"), [['produkt_id' => $produkt->id, 'produkt_nazev' => $produkt->nazev, 'produkt_cena' => $produkt->cena100 / 100, 'kombinace_id' => $pvk0, 'ks' => 1]]));
        }
        else{
            $novaKs = $polozka['ks'] + 1;
            $novaPolozka = ['produkt_id' => $produkt->id, 'produkt_nazev' => $produkt->nazev, 'produkt_cena' => $produkt->cena100 / 100, 'kombinace_id' => $pvk0, 'ks' => $novaKs];

            //odstranime starou polozku
            $seznamBezStare = array_filter($seznam, fn($item) => $item['kombinace_id'] != $pvk0);
            //pridame novou polozku
            $seznamBezStare[] = $novaPolozka;

            $section->set("seznam", $seznamBezStare);
        }
        
        

        if ($this->presenter->isAjax()) {
            $this->presenter->getComponent('kosikNahled')->redrawControl(); //!neni realne chyba
        } else {
            $this->presenter->redirect('this');
        }
    }

}