<?php 

namespace App\Components\VariantyFormComponent;

use App\Components\BaseComponent;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class VariantyFormComponent extends BaseComponent
{
    public int $produktId = 0;

    public array $pv = []; //produkt_varianta
    public array $pvk = []; //produkt_varianta_kombinace
    public array $varianty = [];
    public array $v = []; //varianty

    public function __construct()
    {
        $this->parameters = ['produktId'];
    }

    public function render(): void
    {
        $this->pv = $this->presenter["produkty"]->pv; //no tak tohle je extrem
        $this->pvk = $this->presenter["produkty"]->pvk;
        $this->varianty = $this->presenter["produkty"]->varianty;
        $this->v = $this->presenter["produkty"]->v;

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
        $form->getElementPrototype()->setAttribute("class", "ajax");


        foreach($this->varianty as $key => $varianta){
            $keyV = array_key_first(array_filter($this->v, fn($nazev) => strcmp($nazev, $key) == 0));

            $str = "";
            foreach($this->varianty as $dalsiKey => $dalsiVarianta){
                if (strcmp($dalsiKey, $key) != 0){
                    $dalsiKeyV = array_key_first(array_filter($this->v, fn($nazev) => strcmp($nazev, $dalsiKey) == 0));
                    $str .= "varianta_$dalsiKeyV ";
                }
            }

            $def = $this->presenter->session->getSection("varianty")->get("seznam")["varianta_$keyV"] ?? null;

            $form->addSelect("varianta_$keyV", $key, $varianta)
                ->setPrompt("---")
                ->setHtmlAttribute("data-id", "varianta_$keyV")
                ->setHtmlAttribute("data-depends", $str)
                ->setHtmlAttribute("class", "varianty-select")
                //->setDefaultValue($def)
            ;
        }

        
        $form->addSubmit('zmenitVariantu', '')
            ->setHtmlAttribute('id', 'zmenitVariantu')
            ->setHtmlAttribute('class', 'ajax') 
            ->setHtmlAttribute('style', 'display:none');

        $form->addSubmit('koupitVariantu', 'Koupit')
            ->setHtmlAttribute("class", "btn btn-primary btn-block mt-3 ajax")
        ;

        $form->onValidate[] = [$this, "validace"];
        $form->onSuccess[] = [$this, "koupitVariantu"];

        return $form;
    }

    public function validace($form, $values): void
    {
        //jsou vsechny varianty vybrane?
    }

    public function koupitVariantu($form, $values): void
    {
        Debugger::barDump($values, "Hodnoty pri koupi ve VariantyFormComponent");
        $this->zavrit();
    }

    public function zavrit(){
        Debugger::barDump("Zaviram modal ve VariantyFormComponent");
        if ($this->presenter->isAjax()) {
            $this->presenter["produkty"]->koupitModal = null;
            $this->presenter["produkty"]->redrawControl('koupitModal');

        } else {
            $this->getPresenter()->redirect('this');
        }
    }
    
    

}