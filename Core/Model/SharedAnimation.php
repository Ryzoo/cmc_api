<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 10.07.18
 * Time: 00:00
 */

namespace Core\Model;


use Core\System\BasicModel;

class SharedAnimation extends BasicModel
{
    protected static $table = "SharedAnimation";
    protected static $fields = ["id","owner_user_id","animation_id","shared_user_id"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "int(11) NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $owner_user_id;
    public $animation_id;
    public $shared_user_id;

}