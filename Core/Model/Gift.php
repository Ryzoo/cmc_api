<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 25.09.18
 * Time: 17:18
 */

namespace Core\Model;

use Core\System\BasicModel;

class Gift extends BasicModel
{
    protected static $table = "Gift";
    protected static $fields = ["id","gift_key","description", "function_name", "used_count" ];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(6) COLLATE utf8_polish_ci NOT NULL",
        "varchar(255) COLLATE utf8_polish_ci NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL",
        "int(11) NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $gift_key;
    public $description;
    public $function_name;
    public $used_count;
}