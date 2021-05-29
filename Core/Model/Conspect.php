<?php
namespace Core\Model;
use Core\System\BasicModel;

class Conspect extends BasicModel
{
    protected static $table = "Conspect";
    protected static $fields = ["id","title","description","date","season","team","coach","weight","time_min","time_max","player_min","player_max","equipment","place","img","user_id","folder_id","conspect_elements","pdf"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(255) COLLATE utf8_polish_ci NOT NULL",
        "text COLLATE utf8_polish_ci NOT NULL",
        "datetime NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "tinytext COLLATE utf8_polish_ci NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL",
        "text COLLATE utf8_polish_ci NOT NULL",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "text COLLATE utf8_polish_ci NOT NULL",
        "varchar(30) COLLATE utf8_polish_ci NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $title;
    public $description;
    public $date;
    public $season;
    public $team;
    public $coach;
    public $weight;
    public $time_min;
    public $time_max;
    public $player_min;
    public $player_max;
    public $equipment;
    public $place;
    public $img;
    public $user_id;
    public $folder_id;
    public $conspect_elements;
    public $pdf;

}