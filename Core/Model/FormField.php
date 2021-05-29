<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 30.09.18
 * Time: 20:59
 */

namespace Core\Model;


use Core\System\BasicModel;

class FormField extends BasicModel
{
    protected static $table = "FormField";
    protected static $fields = ["id","field_type", "form_id", "label", "list_field", "is_required"];
    public static $fieldsType = [
        "INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "VARCHAR(50) NOT NULL",
        "INT(11) NOT NULL",
        "VARCHAR(50) NOT NULL",
        "TEXT NOT NULL",
        "TINYINT(1) DEFAULT 0"
    ];

    public $id;
    public $field_type;
    public $form_id;
    public $label;
    public $list_field;
    public $is_required;

    protected static $relations = [];

    public static function createFromFieldList(int $formId, array $fields)
    {
        foreach($fields as $field){
            $type = $field['type'];
            $name = $field['name'];
            $list = $field['list'];
            $required = $field['required'];

            FormField::create([
                "field_type" => $type,
                "form_id" => $formId,
                "label" => $name,
                "list_field" => $list,
                "is_required" => $required
            ]);
        }
    }
}