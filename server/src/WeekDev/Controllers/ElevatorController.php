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
                    if(!$oElevator->isFloorQueueEmpty()){
                        $oElevator->addToFloorQueue($iDestinationFloor);
                        $iDestinationFloor = $oElevator->getFirstFloorQueue();
                        $oElevator->removeFromQueue();
                    }
                    if($iDestinationFloor !== $oElevator->getCurrentFloor()){
                        if(!$oElevator->isDoorsOpened()){
                            array_shift($arElevatorQueue);
                            $arElevatorData = $oElevator->moveToFloor($iDestinationFloor);
                        }else{
                            $arElevatorData = $oElevator->closeDoors();
                        }
                    }else{
                        array_shift($arElevatorQueue);
                        $arElevatorData = $oElevator->openDoors();
                    }

                    $arElevatorData = array_merge($arElevatorData, $oElevator->getData());
                }
            }
        }

        foreach($this->oBuilding->oElevators as $oElevator){
            if(!$oElevator->isFloorQueueEmpty()){
                $iDestinationFloor = $oElevator->getFirstFloorQueue();

                if($oElevator->isAvailable()){
                    if($iDestinationFloor !== $oElevator->getCurrentFloor()){
                        if(!$oElevator->isDoorsOpened()){
                            $oElevator->removeFromQueue();
                            $arElevatorData = $oElevator->moveToFloor($iDestinationFloor);
                        }else{
                            $arElevatorData = $oElevator->closeDoors();
                        }
                    }else{
                        $oElevator->removeFromQueue();
                        $arElevatorData = $oElevator->openDoors();
                    }

                    $arElevatorData = array_merge($arElevatorData, $oElevator->getData());
                }
            }
        }

        $oResponse->addData(array("elevator" => $arElevatorData));
        $oResponse->addData(array("elevatorQueue" => $arElevatorQueue));

        $ar = [];
        foreach ($this->oBuilding->oElevators as $oElevator) {
            $ar[] = $oElevator->getFloorQueue();
        }
        $oResponse->addData(array("debug" => $ar));

        $oResponse->send();
    }

    private function serveIntermediateStops(Elevator $oElevator, $iDestinationFloor): array
    {
        $arElevatorData = array();
        $iCurrentFloor = $oElevator->getCurrentFloor();

        $arElevatorQueue = $this->arInput["elevatorQueue"];
        foreach($arElevatorQueue as $iFloor){
            if($iFloor !== $iCurrentFloor){
                $oElevator->addToFloorQueue($iFloor);
                $iDestinationFloor = $oElevator->getFirstFloorQueue();
            }
            if($iFloor === $iCurrentFloor){
                $arElevatorData = $oElevator->openDoors();
            }
        }

        if($iDestinationFloor !== $iCurrentFloor){
            $oElevator->addToFloorQueue($iDestinationFloor);
        }else{
            $arElevatorData = $oElevator->openDoors();
        }

        return $arElevatorData;
    }

    public function openElevatorDoors(): void
    {
        $oElevator = $this->oBuilding->oElevators->offsetGet($this->arInput["id"]);
        if(!empty($oElevator)){
            $arElevatorData = array_merge($oElevator->openDoors(), $oElevator->getData());
            $oResponse = new Response(array("elevator" => $arElevatorData));
            $oResponse->send();
        }
    }

    public function closeElevatorDoors(): void
    {
        $oElevator = $this->oBuilding->oElevators->offsetGet($this->arInput["id"]);
        if(!empty($oElevator)){
            $arElevatorData = array_merge($oElevator->closeDoors(), $oElevator->getData());
            $oResponse = new Response(array("elevator" => $arElevatorData));
            $oResponse->send();
        }
    }

    private function findAvailableElevator(int $iFloor): ?Elevator
    {
        $iMinDistance = PHP_INT_MAX;
        $oClosestElevator = null;

        foreach($this->oBuilding->oElevators as $oElevator){
            $iCurrentFloor = $oElevator->getCurrentFloor();
            if($oElevator->isAvailable() || $iCurrentFloor === $iFloor){
                $iDistance = abs($iCurrentFloor - $iFloor);
                if($iDistance < $iMinDistance){
                    $iMinDistance = $iDistance;
                    $oClosestElevator = $oElevator;
                }
            }
        }

        return $oClosestElevator;
    }
}