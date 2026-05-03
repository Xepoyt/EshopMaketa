<?php 

namespace App\Components\DetailComponent;
use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use App\Services\MenaService;
use App\Services\ProduktyService;
use App\Services\StitkyService;
use App\Services\VariantyService;
use App\Components\StitekComponent\StitekComponent;
use App\Components\VariantyFormComponent\VariantyFormComponent;
use App\Components\KoupitBtnComponent\KoupitBtnComponent;
use Tracy\Debugger;

use App\Components\VariantyFormComponent\VariantyFormComponentFactory;
use App\Components\KoupitBtnComponent\KoupitBtnComponentFactory;

class DetailComponent extends BaseComponent
{
    public MenaService $menaService;
    private ProduktyService $produktyService;
    private StitkyService $stitkyService;
    private VariantyService $variantyService;
    private VariantyFormComponentFactory $variantyFormComponentFactory;
    private KoupitBtnComponentFactory $koupitBtnComponentFactory;

    public ActiveRow $produkt;
    public array $stitky = [];
    public array $varianty = [];

    public function __construct(MenaService $menaService, ProduktyService $produktyService, StitkyService $stitkyService, VariantyService $variantyService, VariantyFormComponentFactory $variantyFormComponentFactory, KoupitBtnComponentFactory $koupitBtnComponentFactory)
    {
        $this->parameters = ['produkt', 'menaService', 'stitky', 'varianty'];
        $this->menaService = $menaService;
        $this->produktyService = $produktyService;
        $this->stitkyService = $stitkyService;
        $this->variantyService = $variantyService;
        $this->variantyFormComponentFactory = $variantyFormComponentFactory;
        $this->koupitBtnComponentFactory = $koupitBtnComponentFactory;
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderDetail($produkt){
        $this->produkt = $produkt;
        
        $stitkyProProdukty = $this->stitkyService->najdiStitkyProProdukty([$produkt->id]);
        $this->stitky = $stitkyProProdukty[$produkt->id] ?? [];
        Debugger::barDump($this->stitky, 'stitky pro produkt');

        $this->varianty = $this->variantyService->variantyProduktu($produkt->id) ?? [];
        Debugger::barDump($this->varianty, 'varianty pro produkt');

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