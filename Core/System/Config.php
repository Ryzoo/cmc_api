<?php   
namespace Core\System;

class Config{

    private static $configContent;

    public static function config(String $name)
    {
        if(!self::$configContent){
            self::$configContent = file_get_contents(__DIR__."/../../config.json");
        }

        $param = explode(".",$name);
        $config = json_decode(self::$configContent, true);

        foreach ($param as $key => $value) {
            $config = $config[$value];
        }

        return $config;
    }
}