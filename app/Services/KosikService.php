<?php
namespace App\Services;

use Nette\Http\Session;
use Nette\Http\SessionSection;

class KosikService
{
    private SessionSection $section;

    public function __construct(Session $session)
    {
        $this->section = $session->getSection('kosik');
        $this->section->setExpiration('20 minutes');
    }

    public function getSeznam(): array
    {
        return $this->section->get('seznam') ?? [];
    }
    public function setSeznam(array $seznam): void
    {
        $this->section->set('seznam', $seznam);
    }

    public function jePrazdny(): bool
    {
        return empty($this->getSeznam());
    }

    public function vymazat(): void
    {
        $this->section->remove('seznam');
    }

    public function najdiPolozku(int $kombinaceId): ?array
    {
        $seznam = $this->getSeznam();
        $polozka = array_filter($seznam, fn($item) => $item['kombinace_id'] == $kombinaceId);
        return reset($polozka) ?: null;
    }

    public function pridatPolozku(int $produktId, string $produktNazev, int $produktCena100, int $kombinaceId, int $kusy, int $max): void
    {
        $seznam = $this->getSeznam();

        $polozka = $this->najdiPolozku($kombinaceId);

        if(!$polozka){
            $this->setSeznam(array_merge($seznam, [['produkt_id' => $produktId, 'produkt_nazev' => $produktNazev, 'produkt_cena' => $produktCena100 / 100, 'kombinace_id' => $kombinaceId, 'ks' => 1]]));
        }
        else{
            $novaKs = $polozka['ks'] + 1;
            if($novaKs > $max){
                $novaKs = $max;
            }
            $novaPolozka = ['produkt_id' => $produktId, 'produkt_nazev' => $produktNazev, 'produkt_cena' => $produktCena100 / 100, 'kombinace_id' => $kombinaceId, 'ks' => $novaKs];

            //odstranime starou polozku
            $seznamBezStare = array_filter($seznam, fn($item) => $item['kombinace_id'] != $kombinaceId);
            //pridame novou polozku
            $seznamBezStare[] = $novaPolozka;

            $this->setSeznam($seznamBezStare);
        }

        $this->setSeznam($seznam);
    }

    public function odecistPolozku(int $kombinaceId, int $kusy): void
    {
        $seznam = $this->getSeznam();

        $polozka = $this->najdiPolozku($kombinaceId);

        $novaKs = $polozka['ks'] - 1;
        if($novaKs < 0){
            return;
        }
        $novaPolozka = ['produkt_id' => $polozka['produkt_id'], 'produkt_nazev' => $polozka['produkt_nazev'], 'produkt_cena' => $polozka['produkt_cena'], 'kombinace_id' => $polozka['kombinace_id'], 'ks' => $novaKs];

        $seznam[array_search($polozka, $seznam)] = $novaPolozka;

        $this->setSeznam($seznam);
    }
}