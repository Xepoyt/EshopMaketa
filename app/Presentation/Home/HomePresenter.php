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
    public Nette\Http\SessionSection $section;

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

        $this->section = $this->session->getSection("kosik");
        $this->section->setExpiration('20 minutes');

        if($this->section->get("seznam") === null){
            Debugger::barDump('Initializing cart session - presenter');
            $this->section->set("seznam", []);
        }
    }
    
    function createComponentProdukty(): IComponent
    {
        return new ProduktyComponent();
    }

    function createComponentKosikNahled(): IComponent
    {
        return new KosikNahledComponent();
    }
}

