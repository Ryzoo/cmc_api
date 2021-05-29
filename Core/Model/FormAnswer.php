<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 30.09.18
 * Time: 20:59
 */

namespace Core\Model;


use Core\System\BasicModel;

class FormAnswer extends BasicModel
{
    protected static $table = "FormAnswer";
    protected static $fields = ["id","field_id","form_id", "user_id","value"];
    public static $fieldsType = [
        "INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "INT(11) NOT NULL",
        "INT(11) NOT NULL",
        "INT(11) NOT NULL",
        "TEXT NOT NULL",
    ];
    protected static $relations = [];

    public $id;
    public $field_id;
    public $form_id;
    public $user_id;
    public $value;
}