<?php 

namespace App\Components\VariantyFormComponent;

use App\Components\BaseComponent;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use App\Services\ProduktyService;
use App\Services\KosikService;
use App\Services\VyberVariantyService;

class VariantyFormComponent extends BaseComponent
{
    /** @persistent */
    public int $produktId = 0;

    public array $varianty = [];
    public array $variantaData = [];

    public ProduktyService $produktyService;
    public KosikService $kosikService;
    public VyberVariantyService $vyberVariantyService;

    public function __construct(ProduktyService $produktyService, KosikService $kosikService, VyberVariantyService $vyberVariantyService)
    {
        $this->parameters = ['produktId'];
        $this->produktyService = $produktyService;
        $this->kosikService = $kosikService;
        $this->vyberVariantyService = $vyberVariantyService;
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderForm($produktId): void
    {
        $this->produktId = $produktId;
        $this->render();
    }

    public function createComponentVariantyFormForm(): BootstrapForm
    {
        $id = (int) ($this->produktId ?: $this->getPresenter()->getHttpRequest()->getPost('produktId'));
        if($id > 0 && empty($this->varianty)){
            $this->varianty = $this->produktyService->variantyProduktu($id);
            Debugger::barDump($this->varianty, 'VARIANTY PŘED TVORBOU FORMULÁŘE'); // Tady NESMÍ být prázdno!
            $this->variantaData = $this->produktyService->variantaData;
        }

        $form = new BootstrapForm();
        $form->setAjax(true);
        $form->getElementPrototype()->setAttribute("class", "d-flex flex-column flex-sm-row justify-content-start align-items-center gap-4");


        foreach($this->varianty as $key => $varianta){
            $keyVarianta = array_key_first(array_filter($this->variantaData, fn($nazev) => strcmp($nazev, $key) == 0));

            $form->addSelect("varianta_$keyVarianta", $key, $varianta)
                ->setPrompt("---")
                ->setHtmlAttribute("class", "varianty-select")
                ->setRequired()
            ;
        }

        $form->addHidden("produktId", $this->produktId);

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
        $id = (int) $values->produktId;
        if ($id === 0) {
            $form->addError('Kritická chyba: Nepodařilo se identifikovat produkt.');
            $this->redrawControl('variantyForm');
            return;
        }
        Debugger::barDump($id, "ID produktu ve VariantyFormComponent");
        Debugger::barDump($values, "Hodnoty z formulare VariantyFormComponent");
        Debugger::barDump($_POST, 'SUROVÝ POST Z PROHLÍŽEČE');
        Debugger::barDump(array_keys((array) $form->getComponents()), 'NÁZVY PRVKŮ VE FORMULÁŘI');
        $vybraneVarianty = (array)$values;
        unset($vybraneVarianty['koupitVariantu']);
        unset($vybraneVarianty['produktId']);
        Debugger::barDump($vybraneVarianty, "Vybrané varianty ve VariantyFormComponent");

        $vysledek = $this->produktyService->dostupnostKombinace($id, $vybraneVarianty);
        Debugger::barDump($vysledek, "Dostupnost kombinace ve VariantyFormComponent");
        $kombinaceId = $vysledek['kombinaceId'];

        if(!$kombinaceId || $vysledek['ks'] == 0){
            $form->addError("Tato kombinace bohužel již není skladem.");
            $this->redrawControl("variantyForm");
            return;
        }

        $this->produktyService->najdiProduktySkladem();
        $produktySkladem = $this->produktyService->produktySkladem;
        Debugger::barDump($produktySkladem, "Produkty skladem ve VariantyFormComponent");
        $produkt = array_filter($produktySkladem, fn($item) => $item->id == $id);
        $produkt = reset($produkt);

        $max = $this->produktyService->kombinace[$kombinaceId] ?? 0;

        $this->kosikService->pridatPolozku($produkt->id, $produkt->nazev, $produkt->cena100, $kombinaceId, 1, $max);

        $this->zavrit();
    }

    public function zavrit(){
        if ($this->presenter->isAjax()) {
            $this->presenter["produkty"]->koupitModal = null;
            $this->presenter["kosikNahled"]->redrawControl(); //!neni realne chyba
            $this->presenter["produkty"]->redrawControl('koupitModal');
        } else {
            $this->getPresenter()->redirect('this');
        }
    }
    
    public function handleZmenaVariant($id): void
    {
        $request = $this->getPresenter()->getHttpRequest();
        $name = $request->getPost('name');
        $choice = $request->getPost('choice');
        Debugger::barDump($id, "ID produktu");
        Debugger::barDump($name, "Zmena varianty");
        Debugger::barDump($choice);
        $seznam = $this->vyberVariantyService->ulozVolbu($name, $choice);
        $vysledek = $this->produktyService->dostupnostKombinace($id, $seznam);

        if($vysledek['ks']){
            Debugger::barDump($vysledek, "Dostupnost kombinace");
            $this->vyberVariantyService->setKombinaceId($vysledek['kombinaceId']);
        }

        $presenter = $this->getPresenter();

        if($presenter->isAjax()){
            $presenter->sendJson(["ks" => $vysledek['ks']]);
        }
    }

}