<?php

namespace WeekDev\Classes;

use WeekDev\Http\Response;

final class Elevator
{
    private int $iId;
    private int $iCapacity;
    private int $iCurrentFloor;
    private int $iSpeed;
    private int $iHeight;
    private bool $bIsAvailable;
    private bool $bIsMoving;
    private bool $bIsDoorsOpened;
    private array $arPassengers;

    public function __construct(int $iId, int $iCurrentFloor, int $iCapacity, bool $bIsMoving, bool $bIsAvailable, int $iHeight, int $iSpeed, bool $bIsDoorsOpened)
    {
        $this->iId = $iId;
        $this->iCurrentFloor = $iCurrentFloor;
        $this->iCapacity = $iCapacity;
        $this->bIsMoving = $bIsMoving;
        $this->bIsAvailable = $bIsAvailable;
        $this->bIsDoorsOpened = $bIsDoorsOpened;
        $this->iHeight = $iHeight;
        $this->iSpeed = $iSpeed;
    }

    public function moveToFloor($iDestinationFloor): Response
    {
        $this->bIsMoving = true;
        $iTargetPosition = ($iDestinationFloor - 1) * $this->iHeight;
        $iCurrentPosition = ($this->iCurrentFloor - 1) * $this->iHeight;
        $iDistance = abs($iTargetPosition - $iCurrentPosition);
        $iDuration = $iDistance / $this->iSpeed;
        $this->iCurrentFloor = $iDestinationFloor;

        return new Response(array(
            "position" => $iTargetPosition,
            "duration" => $iDuration / 100,
            "action" => __FUNCTION__
        ));
    }

    public function openDoors(): Response
    {
        $this->bIsMoving = false;
        $this->bIsDoorsOpened = true;

        return new Response(array(
            "action" => __FUNCTION__
        ));
    }

    public function closeDoors(): Response
    {
        $this->bIsMoving = false;
        $this->bIsDoorsOpened = false;

        return new Response(array(
            "action" => __FUNCTION__
        ));
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

    public function getData(): array
    {
        return array(
            "id" => $this->iId,
            "currentFloor" => $this->iCurrentFloor,
            "capacity" => $this->iCapacity,
            "isAvailable" => $this->bIsAvailable,
            "isMoving" => $this->bIsMoving,
            "isDoorsOpened" => $this->bIsDoorsOpened
        );
    }
}