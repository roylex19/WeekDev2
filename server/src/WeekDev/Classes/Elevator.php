<?php

namespace WeekDev\Classes;

class Elevator
{
    private int $iCapacity;
    private int $iCurrentFloor;
    private int $iDestinationFloor;
    private array $arPassengers;

    public function __construct($iCapacity)
    {
        $this->iCapacity = $iCapacity;
    }

    public function loadPassenger($oPassenger): void
    {

    }

    public function unloadPassengers(): void
    {

    }

    public function move(): void
    {

    }
}