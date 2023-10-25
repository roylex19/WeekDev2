<?php

namespace WeekDev\Controllers;

use WeekDev\{Classes\Building, Classes\Elevator, Collections\Elevators, Http\Response};

final class ElevatorController extends Controller
{
    private Building $oBuilding;

    public function __construct()
    {
        parent::__construct();

        $this->oBuilding = new Building($this->arInput["elevators"]);
    }

    public function callElevator(): void
    {
        $oResponse = new Response();
        $arElevatorQueue = $this->arInput["elevatorQueue"];

        if(!empty($arElevatorQueue)){
            $iDestinationFloor = current($arElevatorQueue);
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
                        $oResponse = $oElevator->openDoors();
                    }
                }

                $oResponse->addData(array("elevator" => $oElevator->getData()));
            }
        }

        $oResponse->addData(array("elevatorQueue" => $arElevatorQueue));
        $oResponse->send();
    }

    public function sendElevator(): void
    {
        $oResponse = new Response();

        $oElevator = $this->oBuilding->oElevators->offsetGet($this->arInput["id"]);
        if(!empty($oElevator)){
            $iNextFloor = current($oElevator->getFloorQueue());
            if(!$oElevator->isMoving()){
                $oElevator->removeFirstFloor();
                $oResponse = $oElevator->moveToFloor($iNextFloor);
            }
            $oResponse->addData(array("elevator" => $oElevator->getData()));
            $oResponse->send();
        }
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
            if($oElevator->isAvailable()){
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