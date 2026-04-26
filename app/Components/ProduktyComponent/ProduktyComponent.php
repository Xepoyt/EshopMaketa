<?php

namespace App\Components\ProduktyComponent;

use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;
use App\Services\MenaService;
use App\Services\ProduktyService;
use App\Services\StitkyService;
use App\Components\StitekComponent\StitekComponent;
use App\Components\KoupitModalComponent\KoupitModalComponent;
use App\Components\KoupitBtnComponent\KoupitBtnComponent;

class ProduktyComponent extends BaseComponent
{
    public MenaService $menaService;
    public ProduktyService $produktyService;
    public StitkyService $stitkyService;
    public ?ActiveRow $koupitModal = null;

    public array $produktySkladem = [];
    public array $varianty = [];
    public array $stitky = [];

    public function __construct(MenaService $menaService)
    {
        $this->parameters = ['produktySkladem', 'menaService', 'varianty', 'stitky', 'koupitModal'];
        $this->menaService = $menaService;
    }

    public function render(): void
    {
        $this->produktyService = $this->presenter->produktyService;
        $this->stitkyService = $this->presenter->stitkyService;

        $this->produktyService->najdiProduktySkladem();
        $this->produktyService->najdiVarianty();
        $this->stitkyService->najdiStitky();

        $this->produktySkladem = $this->produktyService->produktySkladem;
        $this->varianty = $this->produktyService->varianty;
        $this->stitky = $this->stitkyService->stitky;

        parent::render();
    }

    public function handleKoupit(int $id): void
    {
        //* kosik bude obsahovat pole poli [ActiveRow produkt, int kombinace_id]
        $this->produktyService = $this->presenter->produktyService;
        $section = $this->presenter->session->getSection("kosik");
        $this->produktyService->najdiProduktySkladem();

        $produkt = $this->produktyService->produktModel->najit("id", $id);

        $this->koupitModal = $produkt;
        $this->presenter->session->getSection("varianty")->set("produktId", $id);
        $this->presenter->session->getSection("varianty")->set("seznam", []);

        if ($this->presenter->isAjax()) {
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
        return new KoupitModalComponent($this->menaService);
    }
    
    public function createComponentKoupitBtn(): KoupitBtnComponent
    {
        return new KoupitBtnComponent();
    }
}