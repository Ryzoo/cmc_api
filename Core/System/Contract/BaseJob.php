<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 24.09.18
 * Time: 03:55
 */

namespace Core\System\Contract;

abstract class BaseJob
{
    protected $date;
    protected $name;

    public function __construct(string $name, string $date){
        $this->date = $date;
        $this->name = $name;
    }

    public function getDate(){
        return $this->date;
    }

    public function getName(){
        return $this->name;
    }

    abstract public function closure();
}