<?php

namespace WeekDev;

use Exception;
use WeekDev\Http\{
    Controllers\ElevatorController,
    Request,
    Response
};

final class App
{
    public static function init(): void
    {
        try{
            $oRequest = new Request();
            if(!$oRequest->isAjax()){
                throw new Exception("Request is not ajax");
            }
            $oRequestData = $oRequest->getInput();
            if(!array_key_exists("action", $oRequestData)){
                throw new Exception("Field 'action' not found");
            }
            $sAction = $oRequestData["action"];
            $oElevatorController = new ElevatorController();
            if(!method_exists($oElevatorController, $sAction)){
                throw new Exception("Action not found");
            }
            call_user_func(array($oElevatorController, $sAction));
        }catch(Exception $oException){
            $oResponse = new Response(array("message" => $oException->getMessage()));
            $oResponse->send();
        }
    }
}