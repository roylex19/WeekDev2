<?php

namespace WeekDev\Controllers;

use WeekDev\Http\Request;

class Controller
{
    protected Request $oRequest;
    protected array $arInput;

    public function __construct()
    {
        $oRequest = new Request();
        $this->oRequest = $oRequest;
        $this->arInput = $oRequest->getInput();
    }
}