<?php
namespace App\Components\KoupitModalComponent;

interface KoupitModalComponentFactory
{
    public function create(): KoupitModalComponent;
}