<?php
namespace App\Components\KosikComponent;

interface KosikComponentFactory
{
    public function create(): KosikComponent;
}