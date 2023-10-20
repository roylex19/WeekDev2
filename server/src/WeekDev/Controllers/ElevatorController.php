<?php

namespace WeekDev\Controllers;

use WeekDev\{Classes\Building, Http\Response};

final class ElevatorController extends Controller
{
    private Building $oBuilding;

    public function __construct()
    {
        parent::__construct();

        $this->oBuilding = new Building($this->arRequestData["elevators"]);
    }

    public function callElevator(): Response
    {
        $iFloor = $this->arRequestData["floor"];
        $iElevatorHeight = $this->arRequestData["elevatorHeight"];

        return new Response(array(
            "position" => $iElevatorHeight * ($iFloor - 1)
        ));
    }
}