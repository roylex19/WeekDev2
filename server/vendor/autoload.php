<?php

spl_autoload_register(function($sClassName){
    require_once __DIR__ . "/../src/" . str_replace("\\", "/", $sClassName) . ".php";
});