<?php
namespace App\Components\ProduktyComponent;

interface ProduktyComponentFactory
{
    public function create(): ProduktyComponent;
}