<?php 

namespace App\Components\KoupitModalComponent;

use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use App\Services\MenaService;
use Tracy\Debugger;

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
        Debugger::barDump($this->presenter->produktyComponent->koupitModal, "pred zavrit");
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
            $this->presenter->produktyComponent->koupitModal = null;
            Debugger::barDump($this->presenter->produktyComponent->koupitModal, "po zavrit");
            $this->presenter->produktyComponent->redrawControl('koupitModal');

        } else {
            $this->getPresenter()->redirect('this');
        }
    }
}