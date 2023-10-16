<?php

namespace WeekDev\Controllers;

use WeekDev\Http\Request;

class Controller
{
    protected Request $oRequest;
    protected array $arRequestData;

    public function __construct()
    {
        $oRequest = new Request();
        $this->oRequest = $oRequest;
        $this->arRequestData = $oRequest->getData();
    }
}