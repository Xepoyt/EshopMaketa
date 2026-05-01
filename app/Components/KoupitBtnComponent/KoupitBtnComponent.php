<?php 

namespace App\Components\KoupitBtnComponent;

use App\Components\BaseComponent;
use App\Services\ProduktyService;
use App\Services\KosikService;
use Nette\Database\Table\ActiveRow;

use Tracy\Debugger;

//tohle je pro produkty bez variant
class KoupitBtnComponent extends BaseComponent
{
    public ActiveRow $produkt;
    private ProduktyService $produktyService;
    private KosikService $kosikService;
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

        $this->ks = $this->produktyService->getSklademBezVariant($produkt->id);

        $this->render();
    }

    public function handleKoupit(int $id): void
    {
        $this->kosikService->pridatPolozkuBezVariant($id);

        if ($this->presenter->isAjax()) {
            $this->presenter["kosikNahled"]->redrawControl();
        } else {
            $this->presenter->redirect('this');
        }
    }

}