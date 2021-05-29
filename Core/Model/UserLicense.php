<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.09.18
 * Time: 06:58
 */

namespace Core\Model;

use Core\System\BasicModel;

class UserLicense extends BasicModel
{
    public static $table = "UserLicense";
    public static $fields = ["id", "user_id", "license_id", "date_end"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "date NOT NULL"
    ];
    public static $relations = [
        ['User','user_id','id','user'],
        ['License','license_id','id','license'],
    ];

    public $id;
    public $user_id;
    public $license_id;
    public $date_end;
}