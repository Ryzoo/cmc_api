<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 23.09.18
 * Time: 23:45
 */

namespace Core\System;

use RandomLib\Factory;

class Generator
{
    private static $instance;
    private $generator;
    private static $avilableCharForGenerator = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_";

    private function __clone(){}
    private function __construct(){
        $factory = new Factory;
        $this->generator = $factory->getMediumStrengthGenerator();
    }

    public static function getInstance()
    {
        if(!self::$instance){
            self::$instance = new Generator();
        }

        return self::$instance;
    }

    public static function generateString(int $length)
    {
        return self::getInstance()->generator->generateString($length,self::$avilableCharForGenerator);
    }
}