<?php
namespace App\Components\KoupitBtnComponent;

interface KoupitBtnComponentFactory
{
    public function create(): KoupitBtnComponent;
}