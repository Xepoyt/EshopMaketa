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
use App\Services\ObjednavkaService;
use App\Services\StitkyService;
use App\Services\MenaService;

final class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Http\SessionSection */
    public Nette\Http\SessionSection $sectionK;
    /** @var Nette\Http\SessionSection */
    public Nette\Http\SessionSection $sectionV;

    public ProduktyService $produktyService;
    public MenaService $menaService;
    public ObjednavkaService $objednavkaService;
    public StitkyService $stitkyService;

    public function __construct(
        ProduktyService $produktyService,
        MenaService $menaService,
        ObjednavkaService $objednavkaService,
        StitkyService $stitkyService
    ) {
        parent::__construct();
        
        $this->produktyService = $produktyService;
        $this->produktyService->setPresenter($this);
        $this->menaService = $menaService;
        $this->objednavkaService = $objednavkaService;
        $this->objednavkaService->setPresenter($this);
        $this->stitkyService = $stitkyService;
    }

    function beforeRender()
    {
        parent::beforeRender();

        $this->sectionK = $this->session->getSection("kosik");
        $this->sectionK->setExpiration('20 minutes');

        if($this->sectionK->get("seznam") === null){
            $this->sectionK->set("seznam", []);
        }

        $this->sectionV = $this->session->getSection("varianty");
        $this->sectionV->setExpiration('20 minutes');
    }

    function renderDetail(int $id): void
    {
        $this->template->produkt = $this->produktyService->produktModel->najit("id", $id);
    }
    
    function createComponentProdukty(): IComponent
    {
        return new ProduktyComponent($this->menaService);
    }

    function createComponentKosikNahled(): IComponent
    {
        return new KosikNahledComponent($this->menaService);
    }

    function createComponentDetail(): IComponent
    {
        return new DetailComponent($this->menaService);
    }

    function createComponentKosik(): IComponent
    {
        return new KosikComponent($this->menaService);
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

