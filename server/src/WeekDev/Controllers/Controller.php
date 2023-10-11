<?php

namespace WeekDev\Controllers;

use WeekDev\Http\Request;

class Controller
{
    public Request $oRequest;

    public function __construct()
    {
        $this->oRequest = new Request();
    }
}