<?php

namespace WeekDev\Http;

class Request
{
    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Content-Type: application/json");
    }

    public function getData(): array
    {
        return json_decode(file_get_contents("php://input"), true) ?? array();
    }

    public function isAjax(): bool
    {
        return !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";
    }
}