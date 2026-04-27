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

use App\Components\KoupitModalComponent\KoupitModalComponentFactory;
use App\Components\KoupitBtnComponent\KoupitBtnComponentFactory;

class ProduktyComponent extends BaseComponent
{
    public MenaService $menaService;
    public ProduktyService $produktyService;
    public StitkyService $stitkyService;
    public ?ActiveRow $koupitModal = null;

    public KoupitModalComponentFactory $koupitModalComponentFactory;
    public KoupitBtnComponentFactory $koupitBtnComponentFactory;

    public array $produktySkladem = [];
    public array $varianty = [];
    public array $stitky = [];

    public function __construct(MenaService $menaService, ProduktyService $produktyService, StitkyService $stitkyService, KoupitModalComponentFactory $koupitModalComponentFactory, KoupitBtnComponentFactory $koupitBtnComponentFactory)
    {
        $this->parameters = ['produktySkladem', 'menaService', 'varianty', 'stitky', 'koupitModal'];
        $this->menaService = $menaService;
        $this->produktyService = $produktyService;
        $this->stitkyService = $stitkyService;
        $this->koupitModalComponentFactory = $koupitModalComponentFactory;
        $this->koupitBtnComponentFactory = $koupitBtnComponentFactory;
    }

    public function render(): void
    {
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
        return $this->koupitModalComponentFactory->create();
    }
    
    public function createComponentKoupitBtn(): KoupitBtnComponent
    {
        return $this->koupitBtnComponentFactory->create();
    }
}