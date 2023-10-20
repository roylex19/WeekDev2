<?php

namespace WeekDev\Classes;

use WeekDev\Collections\Elevators;

final class Building
{
    public Elevators $oElevators;

    public function __construct(array $arElevators)
    {
        $oElevators = new Elevators();
        foreach($arElevators as $arElevator){
            $oElevators[] = new Elevator($arElevator["currentFloor"], $arElevator["capacity"]);
        }
        $this->oElevators = $oElevators;
    }
}