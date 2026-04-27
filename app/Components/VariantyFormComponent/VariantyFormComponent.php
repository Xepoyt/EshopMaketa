<?php 

namespace App\Components\VariantyFormComponent;

use App\Components\BaseComponent;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use App\Services\ProduktyService;
use App\Services\KosikService;

class VariantyFormComponent extends BaseComponent
{
    public int $produktId = 0;

    public array $produktVariantaData = []; //produkt_varianta
    public array $produktVariantaKombinaceData = []; //produkt_varianta_kombinace
    public array $varianty = [];
    public array $v = []; //varianty

    public ProduktyService $produktyService;
    public KosikService $kosikService;

    public function __construct(ProduktyService $produktyService, KosikService $kosikService)
    {
        $this->parameters = ['produktId'];
        $this->produktyService = $produktyService;
        $this->kosikService = $kosikService;
    }

    public function render(): void
    {        
        $this->produktyService->najdiProduktySkladem();
        $this->produktyService->najdiVarianty();

        $this->produktVariantaData = $this->produktyService->produktVariantaData; //no tak tohle je extrem
        $this->produktVariantaKombinaceData = $this->produktyService->produktVariantaKombinaceData;
        $this->varianty = $this->produktyService->varianty;
        $this->v = $this->produktyService->variantaData;
        Debugger::barDump($this->produktVariantaData, 'PV ve VariantyFormComponent');
        Debugger::barDump($this->produktVariantaKombinaceData, 'PVK ve VariantyFormComponent');
        Debugger::barDump($this->varianty, 'Varianty ve VariantyFormComponent');

        $this->varianty = $this->varianty[$this->produktId];

        parent::render();
    }

    public function renderForm($produktId): void
    {
        $this->produktId = $produktId;
        $this->render();
    }

    public function createComponentVariantyFormComponent(): BootstrapForm
    {
        $form = new BootstrapForm();
        $form->setAjax(true);
        $form->getElementPrototype()->setAttribute("class", "d-flex flex-column flex-sm-row justify-content-start align-items-center gap-4");


        foreach($this->varianty as $key => $varianta){
            $keyV = array_key_first(array_filter($this->v, fn($nazev) => strcmp($nazev, $key) == 0));

            $form->addSelect("varianta_$keyV", $key, $varianta)
                ->setPrompt("---")
                ->setHtmlAttribute("class", "varianty-select")
                ->setRequired()
            ;
        }

        $form->addSubmit('koupitVariantu', 'Koupit')
            ->setHtmlAttribute("class", "btn btn-primary btn-block disabled")
        ;

        $form->onSuccess[] = [$this, "koupitVariantu"];

        return $form;
    }

    public function validace($form, $values): void
    {
        //jsou vsechny varianty vybrane?
    }

    public function koupitVariantu($form, $values): void
    {
        
        $sectionV = $this->presenter->session->getSection("varianty");
        if($sectionV->get("seznam") === null){
            $sectionV->set("seznam", []);
        }
        $this->produktyService->najdiProduktySkladem();
        $produktySkladem = $this->produktyService->produktySkladem;
        Debugger::barDump($produktySkladem, "Produkty skladem ve VariantyFormComponent");
        $produkt = array_filter($produktySkladem, fn($item) => $item->id == $sectionV->get("produktId"));
        $produkt = reset($produkt);

        $seznam = $this->kosikService->getSeznam();

        $polozka = array_filter($seznam, fn($item) => $item['kombinace_id'] == $sectionV->get("kombinaceId"));
        $polozka = reset($polozka);

        $max = $this->produktyService->kombinace[$sectionV->get("kombinaceId")];

        $this->kosikService->pridatPolozku($produkt->id, $produkt->nazev, $produkt->cena100, $sectionV->get("kombinaceId"), 1, $max);

        $this->zavrit();
    }

    public function zavrit(){
        if ($this->presenter->isAjax()) {
            $this->presenter->session->getSection("varianty")->set("seznam", null);
            $this->presenter["produkty"]->koupitModal = null;
            $this->presenter["kosikNahled"]->redrawControl(); //!neni realne chyba
            $this->presenter["produkty"]->redrawControl('koupitModal');
        } else {
            $this->getPresenter()->redirect('this');
        }
    }
    
    

}