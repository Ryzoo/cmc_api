<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 10.07.18
 * Time: 00:00
 */

namespace Core\Model;


use Core\System\BasicModel;

class Notification extends BasicModel
{
    protected static $table = "Notification";
    protected static $fields = ["id","title","content","icon","url","isRead", "date", "user_id"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(150) NOT NULL",
        "varchar(255) NOT NULL",
        "varchar(50) DEFAULT NULL",
        "varchar(255) DEFAULT NULL",
        "tinyint(4) NOT NULL DEFAULT '1'",
        "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
        "int(11) NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $title;
    public $content;
    public $icon;
    public $url;
    public $isRead;
    public $date;
    public $user_id;


    public static function add($title, $content, ?User $user=null, $url = "")
    {
        if(!$user){
            $user = $GLOBALS['user'];
        }

        Notification::create([
            "user_id" => $user->get("id"),
            "title" => $title,
            "content" => $content,
            "icon" => null,
            "isRead" => 0,
            "date" => (new \DateTime())->format('Y-m-d H:i:s'),
            "url" => $url
        ]);
    }
}