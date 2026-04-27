<?php
namespace App\Components\VariantyFormComponent;

interface VariantyFormComponentFactory
{
    public function create(): VariantyFormComponent;
}