<?php
namespace Core\Model;

use Core\System\BasicModel;

class Contact extends BasicModel{

    protected static $table = "Contact";
    protected static $fields = ["id","country","city","street"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(50) NOT NULL",
        "varchar(50) NOT NULL",
        "varchar(50) NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $country;
    public $city;
    public $street;

}