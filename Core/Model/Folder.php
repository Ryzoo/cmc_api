<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 25.09.18
 * Time: 17:18
 */

namespace Core\Model;


use Core\System\BasicModel;

class Folder extends BasicModel
{
    protected static $table = "Folder";
    protected static $fields = ["id","name","type", "user_id" ];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(255) COLLATE utf8_polish_ci NOT NULL",
        "varchar(255) COLLATE utf8_polish_ci NOT NULL",
        "int(11) NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $name;
    public $type;
    public $user_id;
}