<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use App\Components\KosikNahledComponent\ProduktyComponent as KosikNahledComponentProduktyComponent;
use Nette;
use Nette\ComponentModel\IComponent;
use App\Components\ProduktyComponent\ProduktyComponent;
use App\Components\KosikNahledComponent\KosikNahledComponent;
use App\Components\DetailComponent\DetailComponent;
use App\Components\KosikComponent\KosikComponent;
use Tracy\Debugger;

use App\Services\ProduktyService;

use App\Components\ProduktyComponent\ProduktyComponentFactory;
use App\Components\KosikNahledComponent\KosikNahledComponentFactory;
use App\Components\DetailComponent\DetailComponentFactory;
use App\Components\KosikComponent\KosikComponentFactory;

final class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @var ProduktyService */
    public ProduktyService $produktyService;

    /** @var ProduktyComponentFactory */
    public ProduktyComponentFactory $produktyComponentFactory;
    /** @var KosikNahledComponentFactory */
    public KosikNahledComponentFactory $kosikNahledComponentFactory;
    /** @var DetailComponentFactory */
    public DetailComponentFactory $detailComponentFactory;
    /** @var KosikComponentFactory */
    public KosikComponentFactory $kosikComponentFactory;

    public function __construct(
        ProduktyService $produktyService,
        ProduktyComponentFactory $produktyComponentFactory,
        KosikNahledComponentFactory $kosikNahledComponentFactory,
        DetailComponentFactory $detailComponentFactory,
        KosikComponentFactory $kosikComponentFactory
    ) {
        parent::__construct();
        
        $this->produktyService = $produktyService;

        $this->produktyComponentFactory = $produktyComponentFactory;
        $this->kosikNahledComponentFactory = $kosikNahledComponentFactory;
        $this->detailComponentFactory = $detailComponentFactory;
        $this->kosikComponentFactory = $kosikComponentFactory;
    }

    function renderDetail(int $id): void
    {
        $this->template->produkt = $this->produktyService->produktModel->najit("id", $id);
    }
    
    function createComponentProdukty(): IComponent
    {
        return $this->produktyComponentFactory->create();
    }

    function createComponentKosikNahled(): IComponent
    {
        return $this->kosikNahledComponentFactory->create();
    }

    function createComponentDetail(): IComponent
    {
        return $this->detailComponentFactory->create();
    }

    function createComponentKosik(): IComponent
    {
        return $this->kosikComponentFactory->create();
    }
}

