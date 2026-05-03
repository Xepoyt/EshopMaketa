<?php

namespace App\Components\ProduktyComponent;

use Nette\Utils\Paginator;
use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;
use App\Services\MenaService;
use App\Services\ProduktyService;
use App\Services\StitkyService;
use App\Models\ProduktModel\ProduktModel;
use App\Components\StitekComponent\StitekComponent;
use App\Components\KoupitModalComponent\KoupitModalComponent;
use App\Components\KoupitBtnComponent\KoupitBtnComponent;

use App\Components\KoupitModalComponent\KoupitModalComponentFactory;
use App\Components\KoupitBtnComponent\KoupitBtnComponentFactory;

class ProduktyComponent extends BaseComponent
{
    public MenaService $menaService;
    private ProduktyService $produktyService;
    private StitkyService $stitkyService;
    private ProduktModel $produktModel;
    public ?ActiveRow $koupitModal = null;

    private KoupitModalComponentFactory $koupitModalComponentFactory;
    private KoupitBtnComponentFactory $koupitBtnComponentFactory;

    public array $produktySkladem = [];
    public array $varianty = [];
    public array $stitky = [];

    public function __construct(MenaService $menaService, ProduktyService $produktyService, StitkyService $stitkyService, ProduktModel $produktModel, KoupitModalComponentFactory $koupitModalComponentFactory, KoupitBtnComponentFactory $koupitBtnComponentFactory)
    {
        $this->parameters = ['produktySkladem', 'menaService', 'varianty', 'stitky', 'koupitModal'];
        $this->menaService = $menaService;
        $this->produktyService = $produktyService;
        $this->stitkyService = $stitkyService;
        $this->produktModel = $produktModel;
        $this->koupitModalComponentFactory = $koupitModalComponentFactory;
        $this->koupitBtnComponentFactory = $koupitBtnComponentFactory;
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderSeznam(array $produkty, Paginator $paginator): void
    {
        $this->template->paginator = $paginator;
        $this->template->produktySkladem = $produkty;
        $this->template->menaService = $this->menaService;
        $this->template->stitkyService = $this->stitkyService;

        $this->template->render(__DIR__ . '/ProduktyComponent.latte');
    }

    public function handleKoupit(int $id): void
    {
        $produkt = $this->produktModel->najit("id", $id);

        $this->koupitModal = $produkt;

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