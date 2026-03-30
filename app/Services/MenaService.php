<?php

namespace App\Services;

use Nette\Caching\Cache;
use Nette\Caching\Storage;

class MenaService
{
    private Cache $cache;

    public function __construct(Storage $storage)
    {
        $this->cache = new Cache($storage, 'mena');
    }

    public function CZKtoEUR(float $castka): float {
        $kurz = $this->cache->load('CZKtoEUR', function (&$dependencies) {
            $dependencies[Cache::Expire] = '1 hour';
            return $this->nactiKurzCZKtoEUR();
        });

        return $castka / $kurz;
        
    }

    private function nactiKurzCZKtoEUR(): float {
        $file = "https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt";
        if (($data = @file_get_contents($file)) === false) {
            throw new \Exception("Nepodařilo se načíst kurz CZK -> EUR");
        }
        $radky = explode("\n", $data);
        unset($radky[0]);

        foreach ($radky as $radek){
            $sloupce = explode("|", $radek);
            if($sloupce[3] === "EUR"){
                $kurz = floatval(str_replace(",", ".", $sloupce[4])) / floatval($sloupce[2]);
                return $kurz;
            }
        }
        throw new \Exception("Nenalezen kurz pro EUR");

    }
}