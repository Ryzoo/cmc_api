<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.10.18
 * Time: 21:31
 */

namespace Core\Controller;


use Core\Middleware\Auth;
use Core\Model\File;
use Core\Model\Watermark;
use Core\System\Contract\IController;
use Core\System\FileManager;
use Core\System\Request;
use Core\System\Response;
use Core\System\Validator;

class WatermarkController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getAll(Request $request)
    {
        $watermarks = Watermark::where("user_id","=",$GLOBALS["user"]->id)->get($request);
        FileManager::loadFileFromField($watermarks,["image"],true);
        Response::json($watermarks);
    }

    public function add(Request $request)
    {
        Validator::validateRequest($request)
            ->get("image")->isNotNull();

        $img = $request->get("image");

        FileManager::validateSize($img,2);
        $newFile = FileManager::putFile($img,["image/png","image/jpeg"]);

        $watermark = Watermark::create([
            "user_id" => $GLOBALS["user"]->id,
            "image" => $newFile->getId()
        ]);

        FileManager::loadFileFromField($watermark,["image"],true);
        Response::json($watermark);
    }

    public function remove(Request $request, int $id)
    {
        $watermark = Watermark::find($id);

        if($watermark){
            $img = \Core\System\File::getById($watermark->image);
            $img->delete();
            $watermark->delete();
            Response::json(true);
        }else{
            Response::error("Nie ma takiego watermarku");
        }
    }

}