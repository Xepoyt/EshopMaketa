<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use App\Components\KosikNahledComponent\ProduktyComponent as KosikNahledComponentProduktyComponent;
use Nette;
use Nette\ComponentModel\IComponent;
use App\Components\ProduktyComponent\ProduktyComponent;
use App\Components\KosikNahledComponent\KosikNahledComponent;
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

final class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Http\SessionSection */
    public Nette\Http\SessionSection $sectionK;
    /** @var Nette\Http\SessionSection */
    public Nette\Http\SessionSection $sectionV;

    /** @var KombinaceModel */
    public KombinaceModel $kombinace;
    /** @var ObjednavkaKombinaceModel */
    public ObjednavkaKombinaceModel $objednavkaKombinace;
    /** @var ObjednavkaModel */
    public ObjednavkaModel $objednavka;
    /** @var ProduktModel */
    public ProduktModel $produkt;
    /** @var ProduktStitekModel */
    public ProduktStitekModel $produktStitek;
    /** @var ProduktVariantaKombinaceModel */
    public ProduktVariantaKombinaceModel $produktVariantaKombinace;
    /** @var ProduktVariantaModel */
    public ProduktVariantaModel $produktVarianta;
    /** @var StitekModel */
    public StitekModel $stitek;
    /** @var VariantaModel */
    public VariantaModel $varianta;

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
        $this->kombinace = $kombinace;
        $this->objednavkaKombinace = $objednavkaKombinace;
        $this->objednavka = $objednavka;
        $this->produkt = $produkt;
        $this->produktStitek = $produktStitek;
        $this->produktVariantaKombinace = $produktVariantaKombinace;
        $this->produktVarianta = $produktVarianta;
        $this->stitek = $stitek;
        $this->varianta = $varianta;
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
    
    function createComponentProdukty(): IComponent
    {
        return new ProduktyComponent();
    }

    function createComponentKosikNahled(): IComponent
    {
        return new KosikNahledComponent();
    }

    //kdyz mam normalni link a ne plink a handler jinde, tak to nic nepreda ???halo?
    //a taky proc ty ajax dotazy trva treba tri sekundy??
    public function handleZmenaVariant($name, $choice): void
    {
        
        $this["produkty"]->najdiProduktySkladem();
        $sectionV = $this->session->getSection("varianty");
        $produktId = $sectionV->get("produktId");
        Debugger::barDump($produktId);
        $pv = $this["produkty"]->pv;
        $pvk = $this["produkty"]->fullPvk;
        Debugger::barDump($pvk, "PVK v handleZmenaVariant");
        $options = [];

        
        if($sectionV->get("seznam") === null){
            $sectionV->set("seznam", []);
        }
        $seznam = $sectionV->get("seznam") ?? [];

        if (strcmp($choice, "---") == 0 || $choice === null || strcmp($choice, "") == 0) {
            unset($seznam[$name]);   // ----> TADY JE TEN ZÁSADNÍ BOD
        } else {
            $seznam[$name] = $choice;
        }

        $sectionV->set("seznam", $seznam);

        Debugger::barDump($sectionV->get("seznam"), "SectionV po zmene $name");

        Debugger::barDump($name, "Zmena varianty");
        Debugger::barDump($choice);

        Debugger::barDump($pv);
        foreach($sectionV->get("seznam") as $key => $value){
            Debugger::barDump($key, "Klic v session");
            Debugger::barDump($value, "Hodnota v session");

            if($value === null || strcmp($value, "---") == 0){
                $produktVarianty = array_filter($pv, fn($item) => $item->produkt_id == $produktId && $item->varianta_id == intval(str_replace("varianta_", "", $key)));
            }
            else {
                $produktVarianty = array_filter($pv, fn($item) => $item->produkt_id == $produktId && strcmp($item->varianta_hodnota, $value) == 0 && $item->varianta_id == intval(str_replace("varianta_", "", $key)));
            }
            Debugger::barDump($produktVarianty, "ProduktVarianta pro $key => $value");
            $optionsA = [];
            foreach($produktVarianty as $produktVarianta){
                $kombinaceIds = array_filter($pvk, fn($item) => $item == $produktVarianta->id, ARRAY_FILTER_USE_KEY);
                $kombinaceIds = reset($kombinaceIds);
                Debugger::barDump($kombinaceIds, "KombinaceIds po vyberu $name");

                foreach($kombinaceIds as $kombinaceId){
                    $pvk0 = array_filter($pvk, fn($item) => in_array($kombinaceId, $item));
                    Debugger::barDump($pvk0, "PVK0 pro kombinaceId $kombinaceId");
                    foreach($pvk0 as $pvkKey => $pvkVal){
                        $vKey = intval(str_replace("varianta_", "", $name));
                        if($pv[$pvkKey]->varianta_id == $vKey){
                            continue;
                        }

                        $pvKey = $pv[$pvkKey]->varianta_id;
                        Debugger::barDump($pvKey, "PVKey ve vytvareni options pro $name");

                        $str = "";
                        $str .= "<option";
                        foreach($sectionV->get("seznam") as $sName => $sChoice){
                            $sKey = intval(str_replace("varianta_", "", $sName));
                            if($pv[$pvkKey]->varianta_id == $sKey && strcmp($pv[$pvkKey]->varianta_hodnota, $sChoice) == 0){
                                $str .= " selected";
                            }
                        }
                        $str .= ">" . $pv[$pvkKey]->varianta_hodnota . "</option>";
                        $optionsA[$pvKey][] = $str;
                        Debugger::barDump($str, "Pridavany option pro $name");
                        Debugger::barDump($optionsA);
                    }
                }
            }
            Debugger::barDump($pv);
            Debugger::barDump($produktVarianta);
            Debugger::barDump($optionsA, "OptionsA po vyberu $name");
            foreach($optionsA as $optKey => $optValues){
                $options[$optKey] = "<option>---</option>";
                $options[$optKey] .= implode("", array_unique($optValues));
            }
        }

        if($this->isAjax()){
            Debugger::barDump($options, "Options po zmene $name");
            $this->sendJson(["options" => $options]);
        }
    }
}

