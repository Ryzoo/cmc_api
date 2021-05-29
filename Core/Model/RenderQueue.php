<?php
namespace Core\Model;

use Core\System\BasicModel;

class RenderQueue extends BasicModel{

    protected static $table = "RenderQueue";
    protected static $fields = ["id", "animation_id", "is_render", "is_end", "file_name", "user_dir", "new_name", "date_add"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "tinyint(4) NOT NULL DEFAULT '0'",
        "tinyint(4) NOT NULL DEFAULT '0'",
        "varchar(255) NOT NULL",
        "varchar(255) NOT NULL",
        "varchar(255) NOT NULL",
        "date DEFAULT NULL"
    ];

    protected static $relations = [];

    public $id;
    public $animation_id;
    public $is_render;
    public $is_end;
    public $file_name;
    public $user_dir;
    public $new_name;
    public $date_add;
}