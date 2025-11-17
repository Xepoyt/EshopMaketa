<?php 

namespace App\Components\VariantyFormComponent;

use App\Components\BaseComponent;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use App\Services\ProduktyService;

class VariantyFormComponent extends BaseComponent
{
    public int $produktId = 0;

    public array $pv = []; //produkt_varianta
    public array $pvk = []; //produkt_varianta_kombinace
    public array $varianty = [];
    public array $v = []; //varianty

    public ProduktyService $produktyService;

    public function __construct()
    {
        $this->parameters = ['produktId'];
    }

    public function render(): void
    {
        $this->produktyService = $this->presenter->produktyService;
        
        $this->produktyService->najdiProduktySkladem();
        $this->produktyService->najdiVarianty();

        $this->pv = $this->produktyService->pv; //no tak tohle je extrem
        $this->pvk = $this->produktyService->pvk;
        $this->varianty = $this->produktyService->varianty;
        $this->v = $this->produktyService->v;
        Debugger::barDump($this->pv, 'PV ve VariantyFormComponent');
        Debugger::barDump($this->pvk, 'PVK ve VariantyFormComponent');
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
        $form->getElementPrototype()->setAttribute("class", "ajax d-flex flex-column flex-sm-row justify-content-start align-items-center gap-4");


        foreach($this->varianty as $key => $varianta){
            $keyV = array_key_first(array_filter($this->v, fn($nazev) => strcmp($nazev, $key) == 0));

            $form->addSelect("varianta_$keyV", $key, $varianta)
                ->setPrompt("---")
                ->setHtmlAttribute("class", "varianty-select")
                ->setRequired()
            ;
        }

        $form->addSubmit('koupitVariantu', 'Koupit')
            ->setHtmlAttribute("class", "btn btn-primary btn-block ajax disabled ")
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
        $this->produktyService = $this->presenter->produktyService;
        
        $sectionV = $this->presenter->session->getSection("varianty");
        $sectionK = $this->presenter->session->getSection("kosik");
        if($sectionV->get("seznam") === null){
            $sectionV->set("seznam", []);
        }
        $this->produktyService->najdiProduktySkladem();
        $produktySkladem = $this->produktyService->produktySkladem;
        Debugger::barDump($produktySkladem, "Produkty skladem ve VariantyFormComponent");
        $produkt = array_filter($produktySkladem, fn($item) => $item->id == $sectionV->get("produktId"));
        $produkt = reset($produkt);
        $sectionK->set("seznam", array_merge($sectionK->get("seznam"), [['produkt_id' => $produkt->id, 'produkt_nazev' => $produkt->nazev, 'produkt_cena' => $produkt->cena100 / 100, 'kombinace_id' => $sectionV->get("kombinaceId")]]));
        Debugger::barDump($sectionK->get("seznam"), "Seznam ve VariantyFormComponent po koupi varianty");
        $this->zavrit();
    }

    public function zavrit(){
        if ($this->presenter->isAjax()) {
            $this->presenter["produkty"]->koupitModal = null;
            $this->presenter->getComponent('kosikNahled')->redrawControl(); //!neni realne chyba
            $this->presenter["produkty"]->redrawControl('koupitModal');
        } else {
            $this->getPresenter()->redirect('this');
        }
    }
    
    

}