<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.09.18
 * Time: 06:58
 */

namespace Core\Model;

use Core\System\BasicModel;

class Role extends BasicModel
{
    public static $table = "Role";
    public static $fields = ["id", "name", "form_id", "description", "powers"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(50) NOT NULL",
        "int(11) NOT NULL",
        "text NULL",
        "text NULL"
    ];
    public static $relations = [];

    public $id;
    public $name;
    public $form_id;
    public $description;
    public $powers;
}