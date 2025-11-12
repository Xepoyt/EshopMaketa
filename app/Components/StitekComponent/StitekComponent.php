<?php

namespace App\Components\StitekComponent;

use App\Components\BaseComponent;

class StitekComponent extends BaseComponent
{
    public string $text = '';

    public function __construct()
    {
        $this->parameters = ['text'];
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderBadge(string $text): void
    {
        $this->text = $text;
        $this->render();
    }
}