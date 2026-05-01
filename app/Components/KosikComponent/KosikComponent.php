<?php

namespace App\Components\KosikComponent;

use App\Components\BaseComponent;
use App\Components\StitekComponent\StitekComponent;
use App\Services\ProduktyService;
use App\Services\ObjednavkaService;
use App\Services\MenaService;
use App\Services\KosikService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums;
use Tracy\Debugger;

class KosikComponent extends BaseComponent
{
    public MenaService $menaService;
    private ObjednavkaService $objednavkaService;
    private KosikService $kosikService;
    public array $kosik = [];
    public int $celkemKS = 0;
    public float $celkemCZK = 0.0;

    function __construct(MenaService $menaService, ObjednavkaService $objednavkaService, KosikService $kosikService){
        $this->parameters = ['kosik', 'menaService', 'celkemKS', 'celkemCZK'];

        $this->menaService = $menaService;
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
        $this->kosik = $this->kosikService->getObsahKosiku();
        $this->celkemKS = $this->kosikService->getCelkemKS();
        $this->celkemCZK = $this->kosikService->getCelkovaCena();
    }

    public function createComponentStitekComponent(): StitekComponent
    {
        return new StitekComponent();
    }

    public function handleOdecist($kombinaceId){
        $this->kosikService->odecistPolozku($kombinaceId);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();
        } else {
            $this->presenter->redirect('this');
        }
    }
    public function handlePricist($kombinaceId){
        $this->kosikService->pricistPolozku($kombinaceId);

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
        try{
            $this->objednavkaService->ulozObjednavku($values);
            $this->kosikService->vymazat();
            $this->presenter->flashMessage('Objednávka byla úspěšně vytvořena.', 'success');
        }
        catch(\Exception $e){
            $this->presenter->flashMessage('Při vytváření objednávky došlo k chybě. Zkuste to prosím znovu.', 'danger');
        }

        $this->presenter->redirect('this');
    }
}