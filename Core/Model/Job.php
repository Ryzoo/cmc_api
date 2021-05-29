<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 25.09.18
 * Time: 22:37
 */

namespace Core\Model;

use Core\System\BasicModel;

class Job extends BasicModel
{
    public static $table = "Job";
    public static $fields = ["id","class","name","is_run","date"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(255) COLLATE utf8_polish_ci NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL",
        "tinyint(4) NOT NULL DEFAULT '0'",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL"
    ];
    public static $relations = [];

    public $id;
    public $class;
    public $name;
    public $is_run;
    public $date;
}