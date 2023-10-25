<?php

namespace WeekDev\Http;

use Exception;

final class Response
{
    private array $arResponse;
    private const STATUS_SUCCESS = "success";
    private const STATUS_ERROR = "error";

    public function __construct(array $arResponse = array())
    {
        try{
            http_response_code(200);
            $this->arResponse = $this->getResponseSuccess($arResponse);
        }catch(Exception $oException){
            http_response_code(500);
            $this->arResponse = $this->getResponseError($oException->getMessage());
        }
    }

    private function getResponseSuccess(array $arData): array
    {
        return array(
            "status" => self::STATUS_SUCCESS,
            "data" => $arData,
        );
    }

    private function getResponseError(string $sError): array
    {
        return array(
            "status" => self::STATUS_ERROR,
            "message" => $sError,
        );
    }

    public function addData(array $arData): void
    {
        $this->arResponse["data"] = array_merge($this->arResponse["data"], $arData);
    }

    public function send(): void
    {
        exit(json_encode($this->arResponse));
    }
}