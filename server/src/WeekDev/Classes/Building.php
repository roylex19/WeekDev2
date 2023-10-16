<?php

namespace WeekDev\Classes;

class Building
{
    private int $iFloors;
    private array $arElevators;

    public function __construct($iFloors, $iNumElevators, $iElevatorCapacity)
    {
        $this->iFloors = $iFloors;
    }

    public function callElevator($iFloor): void
    {

    }
}