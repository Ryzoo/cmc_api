<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 30.09.18
 * Time: 20:59
 */

namespace Core\Model;


use Core\System\BasicModel;

class Form extends BasicModel
{
    protected static $table = "Form";
    protected static $fields = ["id","name"];
    public static $fieldsType = [
        "INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "VARCHAR(50) NOT NULL"
    ];
    public static $relations = [];

    public $id;
    public $name;

    public static function getById(?int $id)
    {
        if(!$id) return false;
    }
}