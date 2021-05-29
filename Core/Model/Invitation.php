<?php

namespace Core\Model;

use Core\System\BasicModel;

class Invitation extends BasicModel
{
    public static $table = "Invitation";
    public static $fields = ["id","user_id","user2_id","date"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ];
    public static $relations = [];

    public $id;
    public $user_id;
    public $user2_id;
    public $date;

}