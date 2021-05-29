<?php

class ModelsTest extends PHPUnit_Framework_TestCase {
 
    public function testAllModelExtendBasicModel()
    {
        $allModel = scandir(__DIR__ . "/../Core/Models");
        foreach($allModel as $key => $value){
            if (strpos($value, '.php') === false) unset($allModel[$key]);
            else $allModel[$key] = "Core\\Models\\".str_replace(".php","",$allModel[$key]);
        }

        foreach($allModel as $value){
            $implement = is_subclass_of($value,'Core\System\BasicModel');
            $this->assertTrue($implement,$value." - Nie rozszerza Core\System\BasicModel");
        }
    }

}