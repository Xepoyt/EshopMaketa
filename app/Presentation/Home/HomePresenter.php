<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use App\Components\KosikNahledComponent\ProduktyComponent as KosikNahledComponentProduktyComponent;
use Nette;
use Nette\ComponentModel\IComponent;
use App\Components\ProduktyComponent\ProduktyComponent;
use App\Components\KosikNahledComponent\KosikNahledComponent;
use App\Components\DetailComponent\DetailComponent;
use Tracy\Debugger;

use App\Models\KombinaceModel\KombinaceModel;
use App\Models\ObjednavkaKombinaceModel\ObjednavkaKombinaceModel;
use App\Models\ObjednavkaModel\ObjednavkaModel;
use App\Models\ProduktModel\ProduktModel;
use App\Models\ProduktStitekModel\ProduktStitekModel;
use App\Models\ProduktVariantaKombinaceModel\ProduktVariantaKombinaceModel;
use App\Models\ProduktVariantaModel\ProduktVariantaModel;
use App\Models\StitekModel\StitekModel;
use App\Models\VariantaModel\VariantaModel;
use App\Services\ProduktyService;

final class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Http\SessionSection */
    public Nette\Http\SessionSection $sectionK;
    /** @var Nette\Http\SessionSection */
    public Nette\Http\SessionSection $sectionV;

    public ProduktyService $produktyService;

    public function __construct(
        KombinaceModel $kombinace,
        ObjednavkaKombinaceModel $objednavkaKombinace,
        ObjednavkaModel $objednavka,
        ProduktModel $produkt,
        ProduktStitekModel $produktStitek,
        ProduktVariantaKombinaceModel $produktVariantaKombinace,
        ProduktVariantaModel $produktVarianta,
        StitekModel $stitek,
        VariantaModel $varianta
    ) {
        parent::__construct();

        $this->produktyService = new ProduktyService(
            $kombinace,
            $objednavkaKombinace,
            $objednavka,
            $produkt,
            $produktStitek,
            $produktVariantaKombinace,
            $produktVarianta,
            $stitek,
            $varianta
        );
        $this->produktyService->setPresenter($this);
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
        $this->produktyService->najdiProduktySkladem();
        $produktySkladem = $this->produktyService->produktySkladem;
        $produkt = array_filter($produktySkladem, fn($item) => $item->id == $id);
        $produkt = reset($produkt);
        $this->template->produkt = $produkt;
    }
    
    function createComponentProdukty(): IComponent
    {
        return new ProduktyComponent();
    }

    function createComponentKosikNahled(): IComponent
    {
        return new KosikNahledComponent();
    }

    function createComponentDetail(): IComponent
    {
        return new DetailComponent();
    }

    //kdyz mam normalni link a ne plink a handler jinde, tak to nic nepreda ???halo?
    //a taky proc ty ajax dotazy trva treba tri sekundy??
    public function handleZmenaVariant($name, $choice): void
    {
        
        $this->produktyService->najdiProduktySkladem();
        $sectionV = $this->session->getSection("varianty");
        $produktId = $sectionV->get("produktId");
        Debugger::barDump($produktId);
        $k = $this->produktyService->kombinace;
        $pv = $this->produktyService->pv;
        $pvk = $this->produktyService->fullPvk;
        Debugger::barDump($pvk, "PVK v handleZmenaVariant");

        
        if($sectionV->get("seznam") === null){
            $sectionV->set("seznam", []);
        }
        $seznam = $sectionV->get("seznam") ?? [];

        $seznam[$name] = $choice;

        $sectionV->set("seznam", $seznam);

        Debugger::barDump($sectionV->get("seznam"), "SectionV po zmene $name");

        Debugger::barDump($name, "Zmena varianty");
        Debugger::barDump($choice);

        Debugger::barDump($pv);
        
        $kombinaceIds = [];
        foreach($seznam as $key => $value){
            $pv0 = array_filter($pv, fn($item) => $item->produkt_id == $produktId && $item->varianta_id == intval(str_replace("varianta_", "", $key)) && strcmp($item->varianta_hodnota, $value) == 0);
            $pv0 = reset($pv0);
            if(!$pv0){
                continue;
            }
            Debugger::barDump($pv0, "PV0 pro $key - $value");
            $pvk0 = array_filter($pvk, fn($produktVariantaId) => $produktVariantaId == $pv0->id, ARRAY_FILTER_USE_KEY);
            $kombinaceIds[] = reset($pvk0);
            Debugger::barDump($pvk0, "PVK0 pro $key - $value");
        }
        $prunik = reset($kombinaceIds);
        foreach($kombinaceIds as $kombinaceId){
            $prunik = array_intersect($prunik, $kombinaceId);
        }
        Debugger::barDump($prunik, "Prunik kombinaci pro momentalni vybrane varianty");

        if(count($prunik) == 0){
            $ks = 0;
        }
        elseif(count($prunik) == 1){
            $kombinaceId = reset($prunik);
            $k0 = array_filter($k, fn($key) => $key == $kombinaceId, ARRAY_FILTER_USE_KEY);
            $ks = reset($k0);
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

