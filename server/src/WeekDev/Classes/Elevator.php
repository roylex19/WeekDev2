<?php

namespace WeekDev\Classes;

final class Elevator
{
    private int $iCapacity;
    private int $iCurrentFloor;
    private int $iDestinationFloor;
    private array $arPassengers;

    public function __construct(int $iCurrentFloor, int $iCapacity)
    {
        $this->iCurrentFloor = $iCurrentFloor;
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

    public function getCapacity(): int
    {
        return $this->iCapacity;
    }
}