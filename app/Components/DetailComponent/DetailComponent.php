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

class DetailComponent extends BaseComponent
{
    public MenaService $menaService;
    public ProduktyService $produktyService;
    public StitkyService $stitkyService;

    public ActiveRow $produkt;
    public array $stitky = [];
    public array $varianty = [];

    public function __construct(MenaService $menaService)
    {
        $this->parameters = ['produkt', 'menaService', 'stitky', 'varianty'];
        $this->menaService = $menaService;
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderDetail($produkt){
        $this->produktyService = $this->presenter->produktyService;
        $this->stitkyService = $this->presenter->stitkyService;

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
    public function createComponentVariantyForm(){
        return new VariantyFormComponent();
    }
    public function createComponentKoupitBtn(): KoupitBtnComponent
    {
        return new KoupitBtnComponent();
    }
}