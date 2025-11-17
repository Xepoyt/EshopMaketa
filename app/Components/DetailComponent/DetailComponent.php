<?php 

namespace App\Components\DetailComponent;
use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use App\Services\MenaService;
use App\Services\ProduktyService;
use App\Components\StitekComponent\StitekComponent;
use App\Components\VariantyFormComponent\VariantyFormComponent;
use App\Components\KoupitBtnComponent\KoupitBtnComponent;
use Tracy\Debugger;

class DetailComponent extends BaseComponent
{
    public MenaService $menaService;
    public ProduktyService $produktyService;
    
    public ActiveRow $produkt;
    public array $stitky = [];
    public array $varianty = [];

    public function __construct()
    {
        $this->parameters = ['produkt', 'menaService', 'stitky', 'varianty'];
        $this->menaService = new MenaService();
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderDetail($produkt){
        $this->produktyService = $this->presenter->produktyService;

        $this->produkt = $produkt;
        $this->produktyService->najdiProduktySkladem();
        $this->produktyService->najdiVarianty();
        $this->produktyService->najdiStitky();

        $this->stitky = $this->produktyService->stitky;
        $this->varianty = $this->produktyService->varianty;

        $this->stitky = array_filter($this->stitky, fn($key) => $key == $produkt->id, ARRAY_FILTER_USE_KEY);
        $this->varianty = array_filter($this->varianty, fn($key) => $key == $produkt->id, ARRAY_FILTER_USE_KEY);

        $this->render();
    }

    function createComponentStitekComponent(): StitekComponent
    {
        return new StitekComponent();
    }
    public function createComponentVariantyForm(){
        return new VariantyFormComponent();
    }
    public function createComponentKoupitBtn(): KoupitBtnComponent
    {
        return new KoupitBtnComponent();
    }
}