<?php

namespace WeekDev;

use WeekDev\{Controllers\ElevatorController, Http\Request, Http\Response};
use Exception;

final class App
{
    public static function init(): void
    {
        $oRequest = new Request();
        $oRequestData = $oRequest->getInput();
        $sAction = $oRequestData["action"];
        $oElevatorController = new ElevatorController();

        try{
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