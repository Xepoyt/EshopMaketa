<?php

namespace App\Components\KosikComponent;

use App\Components\BaseComponent;
use App\Components\StitekComponent\StitekComponent;
use App\Services\ProduktyService;
use App\Services\MenaService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums;
use Tracy\Debugger;

class KosikComponent extends BaseComponent
{
    public ProduktyService $produktyService;
    public MenaService $menaService;
    public array $kosik = [];
    public array $kombinace = [];
    public array $varianty = [];
    public array $stitky = [];
    public int $celkemKS = 0;
    public float $celkemCZK = 0.0;

    function __construct(){
        $this->parameters = ['kosik', 'kombinace', 'varianty', 'stitky', 'menaService', 'celkemKS', 'celkemCZK'];

        $this->menaService = new MenaService();
    }

    function render()
    {
        $this->ziskejKosik();

        parent::render();
    }

    function ziskejKosik(): void
    {
        $this->produktyService = $this->presenter->produktyService;
        $this->produktyService->najdiProduktySkladem();
        $this->produktyService->najdiVarianty();
        $this->produktyService->najdiStitky();

        $this->kombinace = $this->produktyService->kombinace;
        $this->stitky = $this->produktyService->stitky;

        $sectionK = $this->presenter->getSession()->getSection('kosik');
        if(!$sectionK->get('seznam')){
            $sectionK->set('seznam', []);
        }
        $this->kosik = $sectionK->get('seznam');
        Debugger::barDump($this->kosik, 'kosik v KosikComponent');

        $pvk = $this->produktyService->fullPvk;
        $pv = $this->produktyService->pv;
        $v = $this->produktyService->v;
        foreach ($this->kosik as $key => $polozka) {
            $this->celkemKS += $polozka['ks'];
            $this->celkemCZK += $polozka['produkt_cena'] * $polozka['ks'];

            $pvk0 = array_filter($pvk, fn($kombinaceIds) => in_array($polozka['kombinace_id'], $kombinaceIds));
            Debugger::barDump($pvk0, 'pvk0 v kosiku');
            if(!isset($pv[array_key_first($pvk0)]->varianta_id)){ //pokud produkt nema varianty
                continue;
            }
            foreach ($pvk0 as $produktVariantaId => $kombinaceIds) {
                $pv0 =  array_filter($pv, fn($item) => $item->id == $produktVariantaId);
                $pv0 = reset($pv0);
                $nazev = $v[$pv0->varianta_id];
                $hodnota = $pv0->varianta_hodnota;

                $this->varianty[$polozka['kombinace_id']][$nazev][] = $hodnota;
            }
        }
        Debugger::barDump($this->varianty, 'varianty v kosiku');
    }

    public function createComponentStitekComponent(): StitekComponent
    {
        return new StitekComponent();
    }

    //ja fakt nechapu proc ty ajax dotazy tak trvaj ale :/
    public function handleOdecist($kombinaceId){
        $this->produktyService = $this->presenter->produktyService;
        $this->produktyService->najdiProduktySkladem();

        $sectionK = $this->presenter->getSession()->getSection('kosik');
        $seznam = $sectionK->get('seznam');

        $polozka = array_filter($seznam, fn($item) => $item['kombinace_id'] == $kombinaceId);
        $polozka = reset($polozka);

        $novaKs = $polozka['ks'] - 1;
        if($novaKs < 0){
            $novaKs = 0;
        }
        $novaPolozka = ['produkt_id' => $polozka['produkt_id'], 'produkt_nazev' => $polozka['produkt_nazev'], 'produkt_cena' => $polozka['produkt_cena'], 'kombinace_id' => $polozka['kombinace_id'], 'ks' => $novaKs];

        $seznam[array_search($polozka, $seznam)] = $novaPolozka;

        $sectionK->set('seznam', $seznam);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();
        } else {
            $this->presenter->redirect('this');
        }
    }
    public function handlePricist($kombinaceId){
        $this->produktyService = $this->presenter->produktyService;
        $this->produktyService->najdiProduktySkladem();

        $sectionK = $this->presenter->getSession()->getSection('kosik');
        $seznam = $sectionK->get('seznam');

        $polozka = array_filter($seznam, fn($item) => $item['kombinace_id'] == $kombinaceId);
        $polozka = reset($polozka);

        $max = $this->produktyService->kombinace[$kombinaceId];

        $novaKs = $polozka['ks'] + 1;
        if($novaKs > $max){
            $novaKs = $max;
        }
        $novaPolozka = ['produkt_id' => $polozka['produkt_id'], 'produkt_nazev' => $polozka['produkt_nazev'], 'produkt_cena' => $polozka['produkt_cena'], 'kombinace_id' => $polozka['kombinace_id'], 'ks' => $novaKs];

        $seznam[array_search($polozka, $seznam)] = $novaPolozka;

        $sectionK->set('seznam', $seznam);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();
        } else {
            $this->presenter->redirect('this');
        }
    }

    public function createComponentObjednavkaForm(): BootstrapForm
    {
        $form = new BootstrapForm();

        /* nevim co vypada lip
            $form->renderMode = Enums\RenderMode::SIDE_BY_SIDE_MODE;
            $form->getRenderer()->setColumns(5,7);
        */
        $form->getElementPrototype()->setAttribute('class', 'mt-4');

        $form->addText('email', 'E-mail:')
            ->setRequired('Zadejte, prosím, váš e-mail.')
            ->addRule($form::Email, 'Zadejte, prosím, platnou e-mailovou adresu.');

        $form->addText('jmeno', 'Jméno a příjmení:')
            ->setRequired('Zadejte, prosím, vaše jméno a příjmení.');

        $form->addText('telefon', 'Telefon:')
            ->setRequired('Zadejte, prosím, vaše telefonní číslo.')
            ->addRule($form::PatternInsensitive, "Telefonní číslo musí být ve tvaru 123456789", "[0-9]{9}");

        $submit = $form->addSubmit('odeslat', 'Vytvořit objednávku')
            ->setHtmlAttribute('class', 'btn btn-primary mt-3');

        if($this->celkemKS == 0){
            $submit->setDisabled(true);
        }

        $form->onValidate[] = [$this, 'validace'];
        $form->onSuccess[] = [$this, 'objednat'];

        return $form;
    }

    public function validace($form, $values): void
    {
        $this->ziskejKosik();
        if($this->celkemKS == 0){
            $form->addError('V košíku nejsou žádné položky.');
        }
    }

    public function objednat($form, $values): void
    {
        $this->produktyService->ulozObjednavku($values);

        $sectionK = $this->presenter->getSession()->getSection('kosik');
        $sectionK->set('seznam', []);

        $this->presenter->flashMessage('Objednávka byla úspěšně vytvořena.', 'success');
        $this->presenter->redirect('this');
    }
}