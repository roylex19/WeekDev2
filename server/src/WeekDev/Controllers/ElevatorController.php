<?php

namespace WeekDev\Controllers;

use WeekDev\{Classes\Building, Classes\Elevator, Http\Response};

final class ElevatorController extends Controller
{
    private Building $oBuilding;

    public function __construct()
    {
        parent::__construct();

        $this->oBuilding = new Building($this->arInput["elevators"]);
    }

    public function processElevators(): void
    {
        $oResponse = new Response();
        $arElevatorQueue = $this->arInput["elevatorQueue"];
        $arElevatorData = array();

        if(!empty($arElevatorQueue)){
            $iDestinationFloor = reset($arElevatorQueue);
            $oElevator = $this->findAvailableElevator($iDestinationFloor);
            if(!empty($oElevator)){
                if(!$oElevator->isMoving()){
                    if($iDestinationFloor !== $oElevator->getCurrentFloor()){
                        if(!$oElevator->isDoorsOpened()){
                            array_shift($arElevatorQueue);
                            $oResponse = $oElevator->moveToFloor($iDestinationFloor);
                        }else{
                            $oResponse = $oElevator->closeDoors();
                        }
                    }else{
                        array_shift($arElevatorQueue);
                        $oResponse = $this->serveIntermediateStops($oElevator, $iDestinationFloor);
                    }
                }

                $arElevatorData[] = $oElevator->getData();
            }
        }

        foreach($this->oBuilding->oElevators as $oElevator){
            $arInternalQueue = $oElevator->arFloorQueue;
            if(!empty($arInternalQueue)){
                $iDestinationFloor = reset($arInternalQueue);
                if($oElevator->isAvailable()){
                    if($iDestinationFloor !== $oElevator->getCurrentFloor()){
                        if(!$oElevator->isDoorsOpened()){
                            array_shift($oElevator->arFloorQueue);
                            $oResponse = $oElevator->moveToFloor($iDestinationFloor);
                        }else{
                            $oResponse = $oElevator->closeDoors();
                        }
                    }else{
                        array_shift($oElevator->arFloorQueue);
                        $oResponse = $this->serveIntermediateStops($oElevator, $iDestinationFloor);
                    }

                    $arElevatorData[] = $oElevator->getData();
                }
            }
        }

        $oResponse->addData(array("elevators" => $arElevatorData));
        $oResponse->addData(array("elevatorQueue" => $arElevatorQueue));
        $oResponse->send();
    }

    private function serveIntermediateStops(Elevator $oElevator, $iDestinationFloor): Response
    {
        $oResponse = new Response();
        $currentFloor = $oElevator->getCurrentFloor();

        $sharedQueue = $this->arInput["elevatorQueue"];
        foreach ($sharedQueue as $floor) {
            if ($floor != $currentFloor) {
                $oElevator->arFloorQueue[] = $floor;
            }
            if ($floor == $currentFloor) {
                $oResponse = $oElevator->openDoors();
            }
        }

        if ($iDestinationFloor != $currentFloor) {
            $oElevator->arFloorQueue[] = $iDestinationFloor;
        }

        return $oResponse;
    }

    public function openElevatorDoors(): void
    {
        $oElevator = $this->oBuilding->oElevators->offsetGet($this->arInput["id"]);
        if(!empty($oElevator)){
            $oResponse = $oElevator->openDoors();
            $oResponse->addData(array("elevator" => $oElevator->getData()));
            $oResponse->send();
        }
    }

    public function closeElevatorDoors(): void
    {
        $oElevator = $this->oBuilding->oElevators->offsetGet($this->arInput["id"]);
        if(!empty($oElevator)){
            $oResponse = $oElevator->closeDoors();
            $oResponse->addData(array("elevator" => $oElevator->getData()));
            $oResponse->send();
        }
    }

    private function findAvailableElevator(int $iFloor): ?Elevator
    {
        $iMinDistance = PHP_INT_MAX;
        $oClosestElevator = null;

        foreach($this->oBuilding->oElevators as $oElevator){
            if($oElevator->isAvailable() || $oElevator->getCurrentFloor() === $iFloor){
                $iDistance = abs($oElevator->getCurrentFloor() - $iFloor);
                if($iDistance < $iMinDistance){
                    $iMinDistance = $iDistance;
                    $oClosestElevator = $oElevator;
                }
            }
        }

        return $oClosestElevator;
    }
}