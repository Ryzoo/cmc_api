<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.09.18
 * Time: 06:58
 */

namespace Core\Model;

use Core\System\BasicModel;

class Watermark extends BasicModel
{
    public static $table = "Watermark";
    public static $fields = ["id", "user_id", "image"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
    ];
    public static $relations = [];

    public $id;
    public $user_id;
    public $image;
}