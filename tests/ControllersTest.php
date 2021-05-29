<?php

class ControllersTest extends PHPUnit_Framework_TestCase {
 
    public function testAllControllerImplementIController()
    {
        $allModel = scandir(__DIR__ . "/../Core/Controller");
        foreach($allModel as $key => $value){
            if (strpos($value, '.php') === false) unset($allModel[$key]);
            else $allModel[$key] = "Core\\Controller\\".str_replace(".php","",$allModel[$key]);
        }

        foreach($allModel as $value){
            $interfaces = class_implements( $value );
            $implement = isset($interfaces['Core\System\IController']);
            $this->assertTrue($implement,$value." - Nie implementuje Core\System\IController");
        }
    }

}