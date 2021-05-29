<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.07.18
 * Time: 16:57
 */

namespace Core\Model;

use Core\System\BasicModel;

class Message extends BasicModel
{
    public static $table = "Message";
    public static $fields = ["id","user_from","user_to","message","isRead","date"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "text COLLATE utf8_polish_ci NOT NULL",
        "tinyint(4) NOT NULL DEFAULT '0'",
        "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ];
    public static $relations = [];

	public $id;
	public $user_from;
	public $user_to;
	public $message;
	public $isRead;
	public $date;

}