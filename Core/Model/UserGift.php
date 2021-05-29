<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 25.09.18
 * Time: 17:18
 */

namespace Core\Model;

use Core\System\BasicModel;

class UserGift extends BasicModel
{
    protected static $table = "UserGift";
    protected static $fields = ["id","gift_key","user_id", "date"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(6) COLLATE utf8_polish_ci NOT NULL",
        "int(11) NOT NULL",
        "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ];
    protected static $relations = [];

    public $id;
    public $gift_key;
    public $user_id;
    public $date;
}