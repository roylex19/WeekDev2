<?php

namespace WeekDev\Classes;

use WeekDev\Collections\Elevators;
use WeekDev\Http\Response;

final class Elevator
{
    private int $iId;
    private int $iCapacity;
    private int $iCurrentFloor;
    private int $iSpeed;
    private int $iHeight;
    private int $iDirection;
    private bool $bIsAvailable;
    private bool $bIsMoving;
    private bool $bIsDoorsOpened;
    private array $arPassengers;
    public array $arFloorQueue;

    public const DIRECTION_NONE = 0;
    public const DIRECTION_UP = 1;
    public const DIRECTION_DOWN = -1;

    public function __construct(int $iId, int $iCurrentFloor, int $iCapacity, bool $bIsMoving, bool $bIsAvailable, int $iHeight, int $iSpeed, bool $bIsDoorsOpened, array $arFloorQueue)
    {
        $this->iId = $iId;
        $this->iCurrentFloor = $iCurrentFloor;
        $this->iCapacity = $iCapacity;
        $this->bIsMoving = $bIsMoving;
        $this->bIsAvailable = $bIsAvailable;
        $this->bIsDoorsOpened = $bIsDoorsOpened;
        $this->iHeight = $iHeight;
        $this->iSpeed = $iSpeed;
        $this->arFloorQueue = $arFloorQueue;
        $this->iDirection = self::DIRECTION_NONE;
    }

    public function moveToFloor($iDestinationFloor): array
    {
        $this->bIsMoving = true;

        /*$arFloorQueue = $this->getFloorQueue();
        if(!empty($arFloorQueue)){
            $iNextInternalFloor = reset($arFloorQueue);
            if(abs($iNextInternalFloor - $this->iCurrentFloor) < abs($iDestinationFloor - $this->iCurrentFloor)){
                array_shift($this->arFloorQueue);
                $iDestinationFloor = $iNextInternalFloor;
            }
        }*/

        $iTargetPosition = ($iDestinationFloor - 1) * $this->iHeight;
        $iCurrentPosition = ($this->iCurrentFloor - 1) * $this->iHeight;
        $iDistance = abs($iTargetPosition - $iCurrentPosition);
        $iDuration = $iDistance / $this->iSpeed;
        $this->iCurrentFloor = $iDestinationFloor;

        return array(
            "position" => $iTargetPosition,
            "duration" => $iDuration / 100,
            "action" => __FUNCTION__
        );
    }

    public function openDoors(): array
    {
        $this->bIsMoving = false;
        $this->bIsDoorsOpened = true;

        return array(
            "action" => __FUNCTION__
        );
    }

    public function closeDoors(): array
    {
        $this->bIsMoving = false;
        $this->bIsDoorsOpened = false;

        return array(
            "action" => __FUNCTION__
        );
    }

    public function loadPassenger($oPassenger): void
    {

    }

    public function unloadPassengers(): void
    {

    }

    public function getCapacity(): int
    {
        return $this->iCapacity;
    }

    public function getCurrentFloor(): int
    {
        return $this->iCurrentFloor;
    }

    public function isAvailable(): bool
    {
        return $this->bIsAvailable && !$this->bIsDoorsOpened && !$this->bIsMoving;
    }

    public function isMoving(): bool
    {
        return $this->bIsMoving;
    }

    public function isDoorsOpened(): bool
    {
        return $this->bIsDoorsOpened;
    }

    public function getId(): int
    {
        return $this->iId;
    }

    public function getFloorQueue(): array
    {
        return $this->arFloorQueue;
    }

    public function getDirection(): int
    {
        return 1;
    }

    public function setDirection(int $iDirection): void
    {
        $this->iDirection = $iDirection;

        if($iDirection > 0){
            sort($this->arFloorQueue);
        }elseif($iDirection < 0){
            rsort($this->arFloorQueue);
        }
    }

    public function getFirstFloorQueue(): int
    {
        $iDestinationFloor = reset($this->arFloorQueue);
        $iCurrentFloor = $this->getCurrentFloor();

        if($iCurrentFloor > $iDestinationFloor){
            $this->setDirection(Elevator::DIRECTION_DOWN);
        }elseif($iCurrentFloor < $iDestinationFloor){
            $this->setDirection(Elevator::DIRECTION_UP);
        }

        return reset($this->arFloorQueue);
    }

    public function addToFloorQueue(int $iFloor): void
    {
        $this->arFloorQueue[] = $iFloor;
    }

    public function removeFromQueue(): void
    {
        array_shift($this->arFloorQueue);
    }

    public function isFloorQueueEmpty(): bool
    {
        return empty($this->arFloorQueue);
    }

    public function getData(): array
    {
        return array(
            "id" => $this->iId,
            "currentFloor" => $this->iCurrentFloor,
            "capacity" => $this->iCapacity,
            "isAvailable" => $this->bIsAvailable,
            "isMoving" => $this->bIsMoving,
            "isDoorsOpened" => $this->bIsDoorsOpened,
            "floorQueue" => $this->arFloorQueue,
            "direction" => $this->iDirection
        );
    }
}