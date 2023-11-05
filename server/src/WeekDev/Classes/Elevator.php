<?php

namespace WeekDev\Classes;

final class Elevator
{
    private int $iId;
    private int $iCapacity;
    private int $iCurrentFloor;
    private int $iSpeed;
    private int $iHeight;
    private int $iDirection;
    private int $iNumberPassengers;
    private bool $bIsAvailable;
    private bool $bIsMoving;
    private bool $bIsDoorsOpened;
    public array $arFloorQueue;

    public const DIRECTION_NONE = 0;
    public const DIRECTION_UP = 1;
    public const DIRECTION_DOWN = -1;

    public function __construct(array $arElevator)
    {
        $this->iId = $arElevator["id"];
        $this->iCurrentFloor = $arElevator["currentFloor"];
        $this->iCapacity = $arElevator["capacity"];
        $this->bIsMoving = $arElevator["isMoving"];
        $this->bIsAvailable = $arElevator["isAvailable"];
        $this->bIsDoorsOpened = $arElevator["isDoorsOpened"];
        $this->iHeight = $arElevator["height"];
        $this->iSpeed = $arElevator["speed"];
        $this->arFloorQueue = $arElevator["floorQueue"];
        $this->iDirection = self::DIRECTION_NONE;
        $this->iNumberPassengers = $arElevator["numberPassengers"];
    }

    public function moveToFloor($iDestinationFloor): array
    {
        $this->bIsMoving = true;

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

    public function loadPassenger(): array
    {
        ++$this->iNumberPassengers;

        $this->bIsAvailable = $this->iNumberPassengers <= $this->iCapacity;

        return array(
            "action" => __FUNCTION__
        );
    }

    public function unloadPassenger(): array
    {
        --$this->iNumberPassengers;

        if($this->iNumberPassengers < 0){
            $this->iNumberPassengers = 0;
        }

        $this->bIsAvailable = $this->iNumberPassengers <= $this->iCapacity;

        return array(
            "action" => __FUNCTION__
        );
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
        return $this->iDirection;
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
            "direction" => $this->iDirection,
            "numberPassengers" => $this->iNumberPassengers
        );
    }
}