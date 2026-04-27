<?php

namespace App\Components\KosikComponent;

use App\Components\BaseComponent;
use App\Components\StitekComponent\StitekComponent;
use App\Services\ProduktyService;
use App\Services\StitkyService;
use App\Services\ObjednavkaService;
use App\Services\MenaService;
use App\Services\KosikService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums;
use Tracy\Debugger;

class KosikComponent extends BaseComponent
{
    public ProduktyService $produktyService;
    public MenaService $menaService;
    public StitkyService $stitkyService;
    public ObjednavkaService $objednavkaService;
    public KosikService $kosikService;
    public array $kosik = [];
    public array $kombinace = [];
    public array $varianty = [];
    public array $stitky = [];
    public int $celkemKS = 0;
    public float $celkemCZK = 0.0;

    function __construct(MenaService $menaService, ProduktyService $produktyService, StitkyService $stitkyService, ObjednavkaService $objednavkaService, KosikService $kosikService){
        $this->parameters = ['kosik', 'kombinace', 'varianty', 'stitky', 'menaService', 'celkemKS', 'celkemCZK'];

        $this->menaService = $menaService;
        $this->produktyService = $produktyService;
        $this->stitkyService = $stitkyService;
        $this->objednavkaService = $objednavkaService;
        $this->kosikService = $kosikService;
    }

    function render()
    {
        $this->ziskejKosik();

        parent::render();
    }

    function ziskejKosik(): void
    {
        $this->produktyService->najdiProduktySkladem();
        $this->produktyService->najdiVarianty();
        $this->stitkyService->najdiStitky();

        $this->kombinace = $this->produktyService->kombinace;
        $this->stitky = $this->stitkyService->stitky;

        $this->kosik = $this->kosikService->getSeznam();
        Debugger::barDump($this->kosik, 'kosik v KosikComponent');

        $produktVariantaKombinaceData = $this->produktyService->fullProduktVariantaKombinaceData;
        $produktVariantaData = $this->produktyService->produktVariantaData;
        $v = $this->produktyService->variantaData;
        foreach ($this->kosik as $key => $polozka) {
            $this->celkemKS += $polozka['ks'];
            $this->celkemCZK += $polozka['produkt_cena'] * $polozka['ks'];

            $produktVariantaKombinace0 = array_filter($produktVariantaKombinaceData, fn($kombinaceIds) => in_array($polozka['kombinace_id'], $kombinaceIds));
            Debugger::barDump($produktVariantaKombinace0, 'pvk0 v kosiku');
            if(!isset($produktVariantaData[array_key_first($produktVariantaKombinace0)]->varianta_id)){ //pokud produkt nema varianty
                continue;
            }
            foreach ($produktVariantaKombinace0 as $produktVariantaId => $kombinaceIds) {
                $produktVarianta0 =  $this->produktyService->produktVariantaModel->najit("id", $produktVariantaId);
                $nazev = $v[$produktVarianta0->varianta_id];
                $hodnota = $produktVarianta0->varianta_hodnota;

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
        $this->kosikService->odecistPolozku($kombinaceId, 1);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();
        } else {
            $this->presenter->redirect('this');
        }
    }
    public function handlePricist($kombinaceId){
        $this->produktyService->najdiProduktySkladem();

        $polozka = $this->kosikService->najdiPolozku($kombinaceId);

        $max = $this->produktyService->kombinace[$kombinaceId];

        $this->kosikService->pridatPolozku($polozka['produkt_id'], $polozka['produkt_nazev'], $polozka['produkt_cena'] * 100, $polozka['kombinace_id'], 1, $max);

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
        $this->objednavkaService->ulozObjednavku($values);

        $this->kosikService->vymazat();

        $this->presenter->redirect('this');
    }
}