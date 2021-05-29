<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 27.09.18
 * Time: 07:50
 */

namespace Core\Controller;

use Core\Middleware\Auth;
use Core\Model\Animation;
use Core\Model\Conspect;
use Core\Model\Folder;
use Core\System\Contract\IController;
use Core\System\FileManager;
use Core\System\QueryBuilder;
use Core\System\Request;
use Core\System\Response;
use Core\System\Validator;

class FolderController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getAllFromType(Request $request, String $type)
    {
        $folders = Folder::where("user_id","=",$GLOBALS["user"]->id)
            ->where("type","LIKE", $type)
            ->get($request);

        foreach ($folders as &$folder){
            $fid = $folder->id;
            switch ($type){
                case "animations":
                    $table = Animation::getTableName();
                    break;
                case "conspects":
                    $table = Conspect::getTableName();
                    break;
            }

            if($table){
                $count = QueryBuilder::select(["COUNT(id) as count"])->from($table)->where("folder_id","=",$fid)->exec()[0]["count"];
            }else $count = 0;

            $folder->count = $count;
        }

        Response::json($folders);
    }

    public function addFolder(Request $request){
        Validator::validateRequest($request)
            ->get("name")->length(3,255)
            ->get("type")->length(3,255);

        $name = $request->get("name");
        $type = $request->get("type");

        $folders = Folder::where("user_id","=",$GLOBALS["user"]->id)
            ->where("name","LIKE", $name)
            ->where("type","LIKE", $type)
            ->get();

        if(isset($folders) && count($folders) > 0){
            Response::error("Taki folder już istnieje");
        }

        $newFolder = Folder::create([
            "name" => $name,
            "type" => $type,
            "user_id" => $GLOBALS["user"]->id
        ]);

        Response::json($newFolder->get('id'));
    }

    public function editFolder(Request $request, int $id)
    {
        Validator::validateRequest($request)
            ->get("name")->length(3,255);

        $name = $request->get("name");
        $type = $request->get("type");

        $folders = Folder::where("user_id","=",$GLOBALS["user"]->id)
            ->where("name","LIKE", $name)
            ->where("type","LIKE", $type)
            ->get();

        if(isset($folders) && count($folders) > 0){
            Response::error("Taki folder już istnieje");
        }

        $folder = Folder::find($id);
        if($folder){
            $folder->update([
                "name" => $name
            ]);
            Response::json(true);
        }else{
            Response::error("Nie ma takiego folderu");
        }
    }

    public function removeFolder(Request $request, int $id)
    {
        $folder = Folder::find($id);
        if($folder){
            $elements = [];
            switch ($folder->get("type")){
                case "animations":
                    $elements = Animation::where("user_id","=",(int)$GLOBALS["user"]->get("id"))->where("folder_id","=",$folder->id)->get();
                    break;
                case "conspects":
                    $elements = Conspect::where("user_id","=",(int)$GLOBALS["user"]->get("id"))->where("folder_id","=",$folder->id)->get();
                    break;
            }

            foreach ($elements as $element) {
                $element->delete();
            }

            $folder->delete();
            Response::json(true);
        }else{
            Response::error("Nie ma takiego folderu");
        }
    }

    public function get(Request $request, int $id){
        $folder = Folder::find($id);

        if($folder){
            switch ($folder->get("type")){
                case "animations":
                    $animations = QueryBuilder::select(["date_add","description", "name","id","main_image"])->from(Animation::getTableName())->where("user_id","=",(int)$GLOBALS["user"]->id)->where("folder_id","=",$id)->orderBy('date_add', true)->get($request);
                    FileManager::loadFileFromField($animations,["main_image","url"]);
                    Response::json($animations,200);
                    break;
                case "conspects":
                    $conspects = Conspect::where("user_id","=",(int)$GLOBALS["user"]->get("id"))->where("folder_id","=",$id)->orderBy('date', true)->get($request);
                    Response::json($conspects,200);
                    break;
            }
            Response::json([]);
        }else{
            Response::error("Nie ma takiego folderu");
        }
    }
}