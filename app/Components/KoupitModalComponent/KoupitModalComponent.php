<?php 

namespace App\Components\KoupitModalComponent;

use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use App\Services\MenaService;
use Tracy\Debugger;
use App\Components\VariantyFormComponent\VariantyFormComponent;

class KoupitModalComponent extends BaseComponent
{
    public ?ActiveRow $produkt = null;
    public MenaService $menaService;

    public function __construct()
    {
        $this->parameters = ['produkt', 'menaService'];
        $this->menaService = new MenaService();
    }

    public function renderModal($produkt): void
    {
        $this->produkt = $produkt;
        $this->render();
    }

    public function render(): void
    {
        parent::render();
    }

    public function handleZavrit(): void
    {
        if ($this->presenter->isAjax()) {
            $this->presenter->session->getSection("varianty")->set("seznam", null);
            $this->presenter["produkty"]->koupitModal = null;
            $this->presenter["produkty"]->redrawControl('koupitModal');
        } else {
            $this->getPresenter()->redirect('this');
        }
    }

    public function createComponentVariantyForm(){
        return new VariantyFormComponent();
    }
}