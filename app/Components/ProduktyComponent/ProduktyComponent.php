<?php

namespace App\Components\ProduktyComponent;

use App\Components\BaseComponent;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;
use App\Services\MenaService;

class ProduktyComponent extends BaseComponent
{
    public array $produktySkladem = [];
    public array $varianty = [];
    public array $stitky = [];
    public array $pv = []; // produkt_varianta
    public MenaService $menaService;

    public function __construct()
    {
        $this->parameters = ['produktySkladem', 'menaService', 'varianty', 'stitky'];
        $this->menaService = new MenaService();
    }

    public function render(): void
    {
        $this->najdiProduktySkladem();
        $this->najdiVarianty();
        $this->najdiStitky();
        parent::render();
    }

    private function najdiProduktySkladem(): void
    {
        $kombinaceModel = $this->getPresenter()->kombinace;
        $produktVariantaKombinaceModel = $this->getPresenter()->produktVariantaKombinace;
        $produktVariantaModel = $this->getPresenter()->produktVarianta;
        $produktModel = $this->getPresenter()->produkt;

        $kombinace = $kombinaceModel->getZaznamy()->where("kusy > 0")->fetchAll();

        $pvk = [];
        foreach($kombinace as $key => $polozka) {
            $pvk[] = $produktVariantaKombinaceModel->getZaznamy()
                ->where("kombinace_id", $polozka->id)
                ->fetchPairs("produkt_varianta_id", "kombinace_id");
        }
        Debugger::barDump($pvk);
        $produktyId = [];
        foreach($pvk as $key => $produktVariantaKombinacePolozky){
            foreach($produktVariantaKombinacePolozky as $produktVariantaId => $kombinaceId){
                $this->pv[] = $produktVariantaModel->getZaznamy()
                    ->where("id", $produktVariantaId)
                    ->fetchAll();
            }
        }
        foreach($this->pv as $key => $produktVariantaPolozky){
            foreach($produktVariantaPolozky as $produktVariantaPolozka){
                $produktyId[] = $produktVariantaPolozka->produkt_id;
            }
        }
        Debugger::barDump($this->pv);
        $produktyId = array_unique($produktyId);
        Debugger::barDump($produktyId);

        foreach($produktyId as $key => $produktId){
            $this->produktySkladem[] = $produktModel->getZaznamy()
                ->where("id", $produktId)
                ->fetch();
        }
        Debugger::barDump($this->produktySkladem);
    }

    private function najdiVarianty(): void
    {
        $variantaModel = $this->getPresenter()->varianta;
        $v = $variantaModel->getZaznamy()->fetchPairs("id", "nazev");
        //* varianty[produktId => [nazevVarianty => [hodnotaVarianty]]] (varianty = [1 => ["Barva" => ["černá", "bílá"], "Velikost" => ["S", "M", "L"]]])
        foreach($this->pv as $key => $produktVarianta){
            $produktVarianta = reset($produktVarianta);
            if(!array_key_exists($produktVarianta->produkt_id, $this->varianty)){
                $this->varianty[$produktVarianta->produkt_id] = [];
            }
            if(!isset($v[$produktVarianta->varianta_id])){
                continue;
            }
            if(!array_key_exists($v[$produktVarianta->varianta_id], $this->varianty[$produktVarianta->produkt_id])){
                $this->varianty[$produktVarianta->produkt_id][$v[$produktVarianta->varianta_id]] = [];
            }
            $this->varianty[$produktVarianta->produkt_id][$v[$produktVarianta->varianta_id]][] = $produktVarianta->varianta_hodnota;
        }

        foreach($this->varianty as $produktId => $druhyVariant){
            foreach($druhyVariant as $nazevVarianty => $hodnotaVarianty){
                $this->varianty[$produktId][$nazevVarianty] = array_unique($hodnotaVarianty);
            }
        }

        Debugger::barDump($this->varianty);
    }

    private function najdiStitky(): void
    {
        $produktStitekModel = $this->getPresenter()->produktStitek;
        $stitekModel = $this->getPresenter()->stitek;

        $ps = $produktStitekModel->getZaznamy()->fetchAll();
        $s = $stitekModel->getZaznamy()->fetchPairs("id", "text");

        Debugger::barDump($s, 'Stitky');
        Debugger::barDump($ps, 'Produkt Stitky');

        foreach($ps as $key => $produktStitek){
            if(!array_key_exists($produktStitek->produkt_id, $this->stitky)){
                $this->stitky[$produktStitek->produkt_id] = [];
            }
            $this->stitky[$produktStitek->produkt_id][] = $s[$produktStitek->stitek_id];
        }

        Debugger::barDump($this->stitky);

        //TODO: stitky do komponent
    }

    public function handleKoupit(int $id): void
    {
        $section = $this->getPresenter()->session->getSection("kosik");

        if(!$section->get("seznam")) {
            $section->set("seznam", []);
        }

        switch ($id) {
            case 1:
                $section->set("seznam", array_merge($section->get("seznam"), [[$id, 'Produkt 1', 199.99]]));
                break;
            case 2:
                $section->set("seznam", array_merge($section->get("seznam"), [[$id, 'Produkt 2', 299.99]]));
                break;
            case 3:
                $section->set("seznam", array_merge($section->get("seznam"), [[$id, 'Produkt 3', 399.99]]));
                break;

        }

        if ($this->getPresenter()->isAjax()) {
            Debugger::barDump($section->get("seznam"), 'After koupit');

            $this->presenter->getComponent('kosikNahled')->redrawControl(); //neni realne chyba
        } else {
            $this->getPresenter()->redirect('this');
        }
    }
}