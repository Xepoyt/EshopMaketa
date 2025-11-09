<?php

namespace App\Services;

class MenaService
{
    public static function CZKtoEUR(float $castka): float {
        $file = "https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt";
        $data = file_get_contents($file);
        $radky = explode("\n", $data);
        unset($radky[0]);

        foreach ($radky as $radek){
            $sloupce = explode("|", $radek);
            if($sloupce[3] === "EUR"){
                $kurz = floatval(str_replace(",", ".", $sloupce[4])) / floatval($sloupce[2]);
                return $castka / $kurz;
            }
        }
        return 0.0;
    }
}