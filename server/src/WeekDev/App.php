<?php

namespace WeekDev;

use WeekDev\Controllers\ElevatorController;

class App
{
    public static function init(): void
    {
        new ElevatorController(2, 5, 6);
    }
}