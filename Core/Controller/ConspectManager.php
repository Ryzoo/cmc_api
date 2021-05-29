<?php
namespace Core\Controller;

use Core\Models\Conspect;
use Core\Models\SharedConspect;
use Core\Other;
use Core\Other\ConspectBuilder;
use Core\System\FileManager;
use Core\System\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Middlewares\Auth;
use Core\System\Validator;

class ConspectManager implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function get(Request $request,int $id){
        $conspects = Conspect::where("id","=",$id)->get(1);
        return new Response($conspects,200);
    }

    public function update(Request $request,int $id){
        Validator::validateRequest($request)
            ->get("title")->length(1,255)
            ->get("description")->length(1,500)
            ->get("team")->length(1,255)
            ->get("coach")->length(1,255)
            ->get("date")->length(1,255)
            ->get("season")->length(1,255)
            ->get("place")->length(1,255);

        $conspect = Conspect::find($id);
        $conspect->title = $request->get('title');
        $conspect->description = $request->get('description');
        $conspect->date = $request->get('date');
        $conspect->season = $request->get('season');
        $conspect->team = $request->get('team');
        $conspect->coach = $request->get('coach');
        $conspect->weight = $request->get('weight');
        $conspect->time_min = $request->get('time_min');
        $conspect->time_max = $request->get('time_max');
        $conspect->player_min = $request->get('player_min');
        $conspect->player_max = $request->get('player_max');
        $conspect->equipment = json_encode($request->get("equipment") ? $request->get("equipment") : "");
        $conspect->place = $request->get('place');

        if(!$request->get("img")) {
            FileManager::deleteUserFile($conspect->img.".png", null,'conspect/'.$conspect->id);
            $conspect->img = $GLOBALS["generator"]->generateString(25,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
            FileManager::putImgFromData($request->get('img'),$conspect->img.".png",'image/png','conspect/'.$conspect->id);
        }

        $conspect->user_id = $GLOBALS["user"]->id;
        $conspect->conspect_elements = json_encode($request->get('conspect_elements') ? $request->get('conspect_elements') : "");
        $conspect->save();

        return new Response($conspect->id,200);
    }

    public function all(Request $request){
        $conspects = Conspect::where("user_id","=",(int)$GLOBALS["user"]->id)->orderBy('date', true)->get();
        return new Response($conspects,200);
    }

    public function download(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNotNull()
            ->get("theme")->isNotNull();

        $conspect = Conspect::find($request->get("id"));

        $conspectBuilder = new ConspectBuilder\ConspectBuilder( $conspect, $GLOBALS["user"]->unique_token);

        switch ($request->get("theme")){
            case "cmc-theme":
                $conspectBuilder->addTheme(new ConspectBuilder\Themes\CMCTheme());
                break;
            case "pzpn-theme":
                $conspectBuilder->addTheme(new ConspectBuilder\Themes\PZPNTheme());
                break;
            default:
                $conspectBuilder->addTheme(new ConspectBuilder\Themes\CMCTheme());
                break;
        }

        $path = $conspectBuilder->render();

        Response::file($path,str_replace(" ", "_",$conspect.title),200);
    }

    public function delete(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNotNull();

        $conspect = new Conspect();
        $conspect->id = $request->get("id");

        FileManager::deleteUserDir('conspect/'.$conspect->id);

        $conspect->delete();
        return new Response(true,200);
    }

    public function allShared(Request $request){
        $sharedList = SharedConspect::where("shared_user_id", "=",(int)$GLOBALS["user"]->id)->get();
        $sharedString = "";
        foreach ($sharedList as $key => $sh){
            if($key === 0){
                $sharedString .= "'".$sh->conspect_id."'";
            }else $sharedString .= ", "."'".$sh->conspect_id."'";
        }

        $conspects = [];

        if(strlen($sharedString) > 0){
            $conspects = Animation::where("id","IN","(".$sharedString.")")->orderBy('date_add', true)->get();
        }

        return new Response($conspects,200);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("title")->length(1,255)
            ->get("description")->length(1,500)
            ->get("team")->length(1,255)
            ->get("coach")->length(1,255)
            ->get("date")->length(1,255)
            ->get("season")->length(1,255)
            ->get("place")->length(1,255);

        $conspect = new Conspect();
        $conspect->title = $request->get('title');
        $conspect->description = $request->get('description');
        $conspect->date = $request->get('date');
        $conspect->season = $request->get('season');
        $conspect->team = $request->get('team');
        $conspect->coach = $request->get('coach');
        $conspect->weight = $request->get('weight');
        $conspect->time_min = $request->get('time_min');
        $conspect->time_max = $request->get('time_max');
        $conspect->player_min = $request->get('player_min');
        $conspect->player_max = $request->get('player_max');
        $conspect->equipment = json_encode($request->get("equipment") ? $request->get("equipment") : "");
        $conspect->place = $request->get('place');
        $conspect->img = $request->get('img') ? $GLOBALS["generator"]->generateString(25,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") : "";
        $conspect->user_id = $GLOBALS["user"]->id;
        $conspect->conspect_elements = json_encode($request->get('conspect_elements') ? $request->get('conspect_elements') : "");
        $conspect->pdf = $GLOBALS["generator"]->generateString(25,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $conspect->save();

        if($request->get('img')){
            FileManager::putImgFromData($request->get('img'),$conspect->img.".png",'image/png','conspect/'.$conspect->id);
        }

        return new Response($conspect->id,200);
    }

}