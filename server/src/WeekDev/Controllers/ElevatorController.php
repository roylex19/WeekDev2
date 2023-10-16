<?php

namespace WeekDev\Controllers;

use WeekDev\{Classes\Building, Http\Response};

class ElevatorController extends Controller
{
    private Building $oBuilding;

    public function __construct($iFloors, $iNumElevators, $iElevatorCapacity)
    {
        parent::__construct();

        $this->oBuilding = new Building($iFloors, $iNumElevators, $iElevatorCapacity);

        if($this->oRequest->isAjax()){
            $oResponse = new Response($this->arRequestData);
            $oResponse->send();
        }
    }

    public function handleRequest($iFloor, $iDestinationFloor): void
    {

    }

    public function run(): void
    {

    }
}