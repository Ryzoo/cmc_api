<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 25.09.18
 * Time: 17:18
 */

namespace Core\Model;


use Core\System\BasicModel;

class File extends BasicModel
{
    protected static $table = "File";
    protected static $fields = ["id","name","extension", "catalog", "user_id" ];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(255) COLLATE utf8_polish_ci NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL",
        "varchar(255) COLLATE utf8_polish_ci NOT NULL",
        "int(11) NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $name;
    public $extension;
    public $catalog;
    public $user_id;

    public function delete($deleteFile = true): bool
    {
        if($deleteFile){
            $file = \Core\System\File::getById($this->id);
            $file->delete();
        }
        parent::delete();

        return true;
    }
}