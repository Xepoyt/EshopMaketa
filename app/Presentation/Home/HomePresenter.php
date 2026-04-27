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
    /** @var Nette\Http\SessionSection */
    public Nette\Http\SessionSection $sectionV;
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

    function beforeRender()
    {
        parent::beforeRender();

        $this->sectionV = $this->session->getSection("varianty");
        $this->sectionV->setExpiration('20 minutes');
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

    //kdyz mam normalni link a ne plink a handler jinde, tak to nic nepreda ???halo?
    public function handleZmenaVariant($name, $choice): void
    {
        
        $this->produktyService->najdiProduktySkladem();
        $sectionV = $this->session->getSection("varianty");
        $produktId = $sectionV->get("produktId");
        Debugger::barDump($produktId);
        $kombinaceData = $this->produktyService->kombinace;
        $produktVariantaData = $this->produktyService->produktVariantaData;
        $produktVariantaKombinaceData = $this->produktyService->fullProduktVariantaKombinaceData;
        Debugger::barDump($produktVariantaKombinaceData, "PVK v handleZmenaVariant");

        
        if($sectionV->get("seznam") === null){
            $sectionV->set("seznam", []);
        }
        $seznam = $sectionV->get("seznam") ?? [];

        $seznam[$name] = $choice;

        $sectionV->set("seznam", $seznam);

        Debugger::barDump($sectionV->get("seznam"), "SectionV po zmene $name");

        Debugger::barDump($name, "Zmena varianty");
        Debugger::barDump($choice);

        Debugger::barDump($produktVariantaData);
        
        $kombinaceIds = [];
        foreach($seznam as $key => $value){
            $hledanaVarianta = intval(str_replace("varianta_", "", $key));
            $produktVarianta0 = array_filter($produktVariantaData, fn($item) => $item->produkt_id == $produktId && $item->varianta_id == $hledanaVarianta && strcmp($item->varianta_hodnota, $value) == 0);
            $produktVarianta0 = reset($produktVarianta0);
            if(!$produktVarianta0){
                continue;
            }
            Debugger::barDump($produktVarianta0, "PV0 pro $key - $value");
            $kombinaceIds[] = $produktVariantaKombinaceData[$produktVarianta0->id];
        }
        if(empty($kombinaceIds)){
            $prunik = [];
        }
        else{
            $prunik = reset($kombinaceIds);
            foreach($kombinaceIds as $kombinaceId){
                $prunik = array_intersect($prunik, $kombinaceId);
            }
        }
        Debugger::barDump($prunik, "Prunik kombinaci pro momentalni vybrane varianty");

        if(count($prunik) == 0){
            $ks = 0;
        }
        elseif(count($prunik) == 1){
            $kombinaceId = reset($prunik);
            $ks = $kombinaceData[$kombinaceId];
            $sectionV->set("kombinaceId", $kombinaceId);
        } else {
            $ks = null;
        }

        if($this->isAjax()){
            Debugger::barDump($ks, "kusy po zmene $name");
            $this->sendJson(["ks" => $ks]);
        }
    }
}

