<?php 

namespace App\Components\DetailComponent;
use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use App\Services\MenaService;
use App\Services\ProduktyService;
use App\Services\StitkyService;
use App\Components\StitekComponent\StitekComponent;
use App\Components\VariantyFormComponent\VariantyFormComponent;
use App\Components\KoupitBtnComponent\KoupitBtnComponent;
use Tracy\Debugger;

use App\Components\VariantyFormComponent\VariantyFormComponentFactory;
use App\Components\KoupitBtnComponent\KoupitBtnComponentFactory;

class DetailComponent extends BaseComponent
{
    public MenaService $menaService;
    public ProduktyService $produktyService;
    public StitkyService $stitkyService;
    public VariantyFormComponentFactory $variantyFormComponentFactory;
    public KoupitBtnComponentFactory $koupitBtnComponentFactory;

    public ActiveRow $produkt;
    public array $stitky = [];
    public array $varianty = [];

    public function __construct(MenaService $menaService, ProduktyService $produktyService, StitkyService $stitkyService, VariantyFormComponentFactory $variantyFormComponentFactory, KoupitBtnComponentFactory $koupitBtnComponentFactory)
    {
        $this->parameters = ['produkt', 'menaService', 'stitky', 'varianty'];
        $this->menaService = $menaService;
        $this->produktyService = $produktyService;
        $this->stitkyService = $stitkyService;
        $this->variantyFormComponentFactory = $variantyFormComponentFactory;
        $this->koupitBtnComponentFactory = $koupitBtnComponentFactory;
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderDetail($produkt){
        $this->produkt = $produkt;
        $this->produktyService->najdiProduktySkladem();
        $this->produktyService->najdiVarianty();
        $this->stitkyService->najdiStitky();

        $this->stitky = $this->stitkyService->stitky;
        $this->varianty = $this->produktyService->varianty;

        $this->stitky = array_filter($this->stitky, fn($key) => $key == $produkt->id, ARRAY_FILTER_USE_KEY);
        $this->varianty = array_filter($this->varianty, fn($key) => $key == $produkt->id, ARRAY_FILTER_USE_KEY);

        $this->render();
    }

    function createComponentStitekComponent(): StitekComponent
    {
        return new StitekComponent();
    }
    public function createComponentVariantyForm(): VariantyFormComponent{
        return $this->variantyFormComponentFactory->create();
    }
    public function createComponentKoupitBtn(): KoupitBtnComponent
    {
        return $this->koupitBtnComponentFactory->create();
    }
}