<?php
namespace Core\Model;

use Core\System\BasicModel;

class Event extends BasicModel{

    protected static $table = "Event";
    protected static $fields = ["id","title","start","end","color","user_id"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL",
        "datetime NOT NULL",
        "datetime NOT NULL",
        "varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL",
        "int(11) NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $title;
    public $start;
    public $end;
    public $color;
    public $user_id;

}