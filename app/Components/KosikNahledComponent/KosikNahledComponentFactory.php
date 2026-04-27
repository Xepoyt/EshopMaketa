<?php
namespace App\Components\KosikNahledComponent;

interface KosikNahledComponentFactory
{
    public function create(): KosikNahledComponent;
}