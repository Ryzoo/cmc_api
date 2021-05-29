<?php
namespace Core\Model;

use Core\System\BasicModel;

class WalletHistory extends BasicModel{

    public static $table = "WalletHistory";
    public static $fields = ["id","wallet_id","action","status","date"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "varchar(255) COLLATE utf8_polish_ci NOT NULL",
        "float NOT NULL",
        "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ];
    public static $relations = [];

    public $id;
    public $wallet_id;
    public $action;
    public $status;
    public $date;

}

