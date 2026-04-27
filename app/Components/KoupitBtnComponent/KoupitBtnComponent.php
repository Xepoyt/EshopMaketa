<?php 

namespace App\Components\KoupitBtnComponent;

use App\Components\BaseComponent;
use App\Services\ProduktyService;
use App\Services\KosikService;
use Nette\Database\Table\ActiveRow;

use Tracy\Debugger;

class KoupitBtnComponent extends BaseComponent
{
    public ActiveRow $produkt;
    public ProduktyService $produktyService;
    public KosikService $kosikService;
    public int $ks = 0;

    public function __construct(ProduktyService $produktyService, KosikService $kosikService)
    {
        $this->parameters = ['ks', 'produkt'];
        $this->produktyService = $produktyService;
        $this->kosikService = $kosikService;
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderButton($produkt): void
    {
        $this->produkt = $produkt;

        $this->produktyService->najdiProduktySkladem();


        $produktVarianta0 = $this->produktyService->produktVariantaModel->najit("produkt_id", $produkt->id);

        $produktVariantaKombinace0 = $this->produktyService->produktVariantaKombinaceData[$produktVarianta0->id];

        $this->ks = $this->produktyService->kombinace[$produktVariantaKombinace0] ?? 0;

        $this->render();
    }

    public function handleKoupit(int $id): void
    {
        $this->produktyService->najdiProduktySkladem();

        $produkt = $this->produktyService->produktModel->najit("id", $id);

        $produktVarianta0 = $this->produktyService->produktVariantaModel->najit("produkt_id", $produkt->id);

        Debugger::barDump($produktVarianta0, 'produktVarianta0 v KoupitBtnComponent');

        $produktVariantaKombinace0 = $this->produktyService->produktVariantaKombinaceData[$produktVarianta0->id];

        Debugger::barDump($produktVariantaKombinace0, 'produktVariantaKombinace0 v KoupitBtnComponent');
        Debugger::barDump($this->produktyService->produktVariantaKombinaceData, 'produktVariantaKombinaceData v KoupitBtnComponent');

        ////$produktVariantaKombinace0 = reset($produktVariantaKombinace0);

        $max = $this->produktyService->kombinace[$produktVariantaKombinace0];

        $this->kosikService->pridatPolozku($produkt->id, $produkt->nazev, $produkt->cena100, $produktVariantaKombinace0, 1, $max);

        if ($this->presenter->isAjax()) {
            $this->presenter["kosikNahled"]->redrawControl();
        } else {
            $this->presenter->redirect('this');
        }
    }

}