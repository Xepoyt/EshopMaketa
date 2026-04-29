<?php
namespace App\Services;

use Nette\Http\Session;
use Nette\Http\SessionSection;

class VyberVariantyService
{
    private SessionSection $section;

    public function __construct(Session $session)
    {
        $this->section = $session->getSection("varianty");
    }

    public function ulozVolbu(string $name, string $choice): array
    {
        $seznam = $this->section->get("seznam") ?? [];
        $seznam[$name] = $choice;
        $this->section->set("seznam", $seznam);
        return $seznam;
    }

    public function setKombinaceId(int $kombinaceId): void
    {
        $this->section->set("kombinaceId", $kombinaceId);
    }
}