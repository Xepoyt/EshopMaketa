<?php
namespace App\Components\DetailComponent;

interface DetailComponentFactory
{
    public function create(): DetailComponent;
}