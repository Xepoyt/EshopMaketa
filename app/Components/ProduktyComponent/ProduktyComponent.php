<?php

namespace App\Components\ProduktyComponent;

use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;
use App\Services\MenaService;
use App\Services\ProduktyService;
use App\Components\StitekComponent\StitekComponent;
use App\Components\KoupitModalComponent\KoupitModalComponent;

class ProduktyComponent extends BaseComponent
{
    public MenaService $menaService;
    public ProduktyService $produktyService;
    public ?ActiveRow $koupitModal = null;

    public array $produktySkladem = [];
    public array $varianty = [];
    public array $stitky = [];

    public function __construct()
    {
        $this->parameters = ['produktySkladem', 'menaService', 'varianty', 'stitky', 'koupitModal'];
        $this->menaService = new MenaService();
    }

    public function render(): void
    {
        $this->produktyService = $this->presenter->produktyService;

        $this->produktyService->najdiProduktySkladem();
        $this->produktyService->najdiVarianty();
        $this->produktyService->najdiStitky();

        $this->produktySkladem = $this->produktyService->produktySkladem;
        $this->varianty = $this->produktyService->varianty;
        $this->stitky = $this->produktyService->stitky;

        parent::render();
    }

    public function handleKoupit(int $id): void
    {
        //* kosik bude obsahovat pole poli [ActiveRow produkt, int kombinace_id]
        $this->produktyService = $this->presenter->produktyService;
        $section = $this->presenter->session->getSection("kosik");
        $this->produktyService->najdiProduktySkladem();

        if($section->get("seznam") === null) {
            $section->set("seznam", []);
        }

        $produkt = array_filter($this->produktyService->produktySkladem, fn($item) => $item->id == $id);
        $produkt = reset($produkt);

        $pv0 = array_filter($this->produktyService->pv, fn($item) => $item->produkt_id == $id);
        $pv0 = reset($pv0);

        if(!isset($pv0->varianta_id)){
            // produkt nema zadny druh variant
            $pvk0 = array_filter($this->produktyService->pvk, fn($produktVariantaId) => $produktVariantaId == $pv0->id, ARRAY_FILTER_USE_KEY);
            $pvk0 = reset($pvk0);
            $section->set("seznam", array_merge($section->get("seznam"), [['produkt_id' => $produkt->id, 'produkt_nazev' => $produkt->nazev, 'produkt_cena' => $produkt->cena100 / 100, 'kombinace_id' => $pvk0]]));
            $this->koupitModal = null;
            Debugger::barDump($section->get("seznam"), "Seznam v kosiku po koupi bez variant");
        } else {
            $this->koupitModal = $produkt;
            $this->presenter->session->getSection("varianty")->set("produktId", $id);
            $this->presenter->session->getSection("varianty")->set("seznam", []);
        }
        /*
        $section->set("seznam", array_merge($section->get("seznam"), [$this->produktySkladem[$id]]));
        */

        if ($this->presenter->isAjax()) {

            $this->presenter->getComponent('kosikNahled')->redrawControl(); //!neni realne chyba
            $this->redrawControl('koupitModal');
        } else {
            $this->presenter->redirect('this');
        }
    }

    public function createComponentStitekComponent(): StitekComponent
    {
        return new StitekComponent();
    }

    public function createComponentKoupitModalComponent(): KoupitModalComponent
    {
        return new KoupitModalComponent();
    }
}