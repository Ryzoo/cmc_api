<?php
namespace Core\Model;

use Core\System\BasicModel;

class Animation extends BasicModel{

    protected static $table = "Animation";
    protected static $fields = ["id", "name","description","game_field","equipment","age_category","tips","watermark", "user_id","folder_id" ,"main_image" ,"path_field" ,"frame_data" ,"object_in_animation", "url", "date_add"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(255) NOT NULL",
        "text NOT NULL",
        "varchar(50) DEFAULT NULL",
        "mediumtext",
        "varchar(50) DEFAULT NULL",
        "text",
        "text",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "varchar(255) NOT NULL",
        "varchar(255) NOT NULL",
        "longtext NOT NULL",
        "longtext NOT NULL",
        "varchar(255) NOT NULL",
        "date DEFAULT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $name;
    public $description;

    public $game_field;
    public $equipment;
    public $age_category;
    public $tips;
    public $watermark;

    public $user_id;
    public $folder_id;
    public $main_image;
    public $path_field;
    public $frame_data;
    public $object_in_animation;
    public $url;
    public $date_add;

    public static function render(int $animationId, ?User $user = null)
    {
        $animation = Animation::find($animationId);
        if(!$user){
            $user = $GLOBALS["user"];
        }

        $fileId = $animation->get("url");
        $mp4File = \Core\System\File::getById($fileId);

        $fileName = $animation->get("name");
        $userDir = $mp4File->getPath(true);
        $newName = $mp4File->getName(false);

        $currentYear = (new \DateTime())->format("Y");

        RenderQueue::create([
            "animation_id" => $animationId,
            "is_render" => 0,
            "is_end" => 0,
            "file_name" => $fileName,
            "user_dir" => $userDir,
            "new_name" => $newName,
            "date_add" => (new \DateTime())->format("Y-m-d H:i:s"),
        ]);

    }

}