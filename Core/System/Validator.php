<?php
namespace Core\System;

use Core\System\Request;
use Core\System\Response;

class Validator{
    private $requestMode;
    private $request;
    private $requestFromGet;
    private $current;
    private $currentName;

    public function __construct($reqMode=false, $request = null, $elementArray = array(), $requestFromGet = false){
        $this->requestMode = $reqMode;
        $this->request = $request;
        $this->requestFromGet = $requestFromGet;
        $this->elements = $elementArray;
    }

    static public function validateRequest(Request $request,$requestFromGet = false){
        return new Validator(true,$request,array(),$requestFromGet);
    }

    static public function validateElement(Array $elements){
        return new Validator(false,null,$elements);
    }

    public function get($name){
        $this->currentName = $name;
        if($this->requestMode){
            $this->current = $this->request->get($name,$this->requestFromGet);
        }else{
            $this->current = $this->elements[$name];
        }
        return $this;
    }

    private function returnError($errorText){
        Logger::getInstance()->warning("Nieudana walidacja: {$errorText}");
        Response::error($errorText,406);
    }

    public function isNotNull(){
        $ret = !is_null($this->current);
        if(!$ret) $this->returnError("Element {$this->currentName} nie może być pusty");
        return $this;
    }

    public function isString(){
        $this->isNotNull();
        $ret = is_string($this->current);
        if(!$ret) $this->returnError("Element {$this->currentName} musi być stringiem");
        return $this;
    }

    public function isNumber(){
        $this->isNotNull();
        $ret = is_numeric($this->current);
        if(!$ret) $this->returnError("Element {$this->currentName} musi być liczbą");
        return $this;
    }

    public function length($min = 0, $max = null){
        $this->isString();
        $ret = strlen($this->current) >= $min;
        if(!is_null($max)&&$ret) $ret = strlen($this->current) <= $max;
        $maxTxt = (is_null($max)?'nieskonczoność':$max);
        if(!$ret) $this->returnError("Element {$this->currentName} musi mieć długość w przedziale ({$min}-{$maxTxt})");
        return $this;
    }

    public static function validateFormField($form,$listOfField){
        foreach ($listOfField as $field) {
            if(!isset($form[$field]) || !isset($form[$field]['value']) || strlen($form[$field]['value']) <= 0){
                Response::error("W formularzu nie sprecyzowano odpowiednich pól.",406);
            }
        }
    }
}