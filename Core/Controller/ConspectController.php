<?php
namespace Core\Controller;

use Core\Model\Conspect;
use Core\Model\Friend;
use Core\Model\Permission;
use Core\Model\SharedConspect;
use Core\Other;
use Core\Other\ConspectBuilder\ConspectBuilder;
use Core\Other\ConspectBuilder\Themes\CMCTheme;
use Core\Other\ConspectBuilder\Themes\PZPNTheme;
use Core\System\File;
use Core\System\FileManager;
use Core\System\Contract\IController;
use Core\System\Generator;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;

class ConspectController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getOne(Request $request,int $id){
        $conspect = Conspect::find($id);

        if(!isset($conspect)){
            Response::error("Brak konspektu o danym id");
        }

        Response::json($conspect,200);
    }

    public function update(Request $request,int $id){



        Validator::validateRequest($request)
            ->get("title")->length(1,255)
            ->get("description")->length(1,500)
            ->get("team")->length(1,255)
            ->get("folder")->isNotNull()
            ->get("coach")->length(1,255)
            ->get("date")->length(1,255)
            ->get("season")->length(1,255)
            ->get("place")->length(1,255);

        $conspect = Conspect::find($id);

        if($conspect){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$conspect->get("user_id")]
            ]);
        }else{
            Response::error("Nie ma takiego elementu.");
        }

        $oldFile = null;
        if($conspect->get("img")){
            $oldFile = File::getById((int)$conspect->get("img"));
        }
        $newFile = null;

        if($request->get("img")) {
            $newFile = FileManager::putFile($request->get("img"),['image/png', 'image/jpeg'],"conspect/{$conspect->get("id")}");
            if($oldFile){
                $oldFile->delete();
            }
        }

        $conspect->update([
            "img" => $newFile ? $newFile->getId() : ($oldFile ? $oldFile->getId() : null),
            "title" => $request->get('title'),
            "folder_id" => $request->get("folder"),
            "description" => $request->get('description'),
            "date" => $request->get('date'),
            "season" => $request->get('season'),
            "team" => $request->get('team'),
            "coach" => $request->get('coach'),
            "weight" => $request->get('weight'),
            "time_min" => $request->get('time_min'),
            "time_max" => $request->get('time_max'),
            "player_min" => $request->get('player_min'),
            "player_max" => $request->get('player_max'),
            "equipment" => json_encode($request->get("equipment") ? $request->get("equipment") : ""),
            "place" => $request->get('place'),
            "conspect_elements" => json_encode($request->get('conspect_elements') ? $request->get('conspect_elements') : "")
        ]);

        Response::json($conspect,200);
    }

    public function getAll(Request $request){
        $conspects = Conspect::where("user_id","=",(int)$GLOBALS["user"]->get("id"))->orderBy('date', true)->get($request);
        Response::json($conspects,200);
    }

    public function download(Request $request, int $id, string $theme){
        $conspect = Conspect::find($id);
        $conspectBuilder = new ConspectBuilder( $conspect, $GLOBALS["user"]->get("unique_token"));

        switch ($theme){
            case "cmc-theme":
                $conspectBuilder->addTheme(new CMCTheme());
                break;
            case "pzpn-theme":
                $conspectBuilder->addTheme(new PZPNTheme());
                break;
            default:
                $conspectBuilder->addTheme(new CMCTheme());
                break;
        }

        $fileId = $conspectBuilder->render();

        Response::file($fileId);
    }

    public function delete(Request $request, int $id){

        $conspect = Conspect::find($id);
        if($conspect){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$conspect->get("user_id")]
            ]);
        }else{
            Response::error("Nie ma takiego elementu.");
        }

        Conspect::remove($id);
        FileManager::deleteUserDir('conspect/'.$id);
        Response::json(true,200);
    }

    public function getAllShared(Request $request){
        $sharedList = SharedConspect::where("shared_user_id", "=",(int)$GLOBALS["user"]->id)->get();
        $sharedString = "";

        foreach ($sharedList as $key => $sh){
            if($key === 0){
                $sharedString .= "'".$sh->get("conspect_id")."'";
            }else $sharedString .= ", "."'".$sh->get("conspect_id")."'";
        }

        $conspects = [];

        if(strlen($sharedString) > 0){
            $conspects = Conspect::where("id","IN","(".$sharedString.")")->orderBy('date_add', true)->get($request);
        }

        Response::json($conspects,200);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("title")->length(1,255)
            ->get("description")->length(1,500)
            ->get("team")->length(1,255)
            ->get("folder")->isNotNull()
            ->get("coach")->length(1,255)
            ->get("date")->length(1,255)
            ->get("season")->length(1,255)
            ->get("place")->length(1,255);

        $file = null;
        if($request->get('img')){
            $file = FileManager::putFile($request->get('img'), ['image/png', 'image/jpeg']);
        }

        $conspect = Conspect::create([
            "title" => $request->get('title'),
            "folder_id" => $request->get("folder"),
            "description" => $request->get('description'),
            "date" => $request->get('date'),
            "season" => $request->get('season'),
            "team" => $request->get('team'),
            "coach" => $request->get('coach'),
            "weight" => $request->get('weight'),
            "time_min" => $request->get('time_min'),
            "time_max" => $request->get('time_max'),
            "player_min" => $request->get('player_min'),
            "player_max" => $request->get('player_max'),
            "equipment" => json_encode($request->get("equipment") ? $request->get("equipment") : ""),
            "place" => $request->get('place'),
            "img" => $file ? $file->getId() : "",
            "user_id" => $GLOBALS["user"]->get("id"),
            "conspect_elements" => json_encode($request->get('conspect_elements') ? $request->get('conspect_elements') : ""),
            "pdf" => Generator::generateString(25)
        ]);

        if($file){
            $file->move("conspect/{$conspect->get("id")}");
        }

        Response::json($conspect,200);
    }

    public function getSharedUnSharedList(Request $request, int $id){
        $sharedOfThisAnim = SharedConspect::where("conspect_id","=",$id)->get();
        $friendsList = Friend::getUserFriends();
        $sharedFriend = array();
        $sharedFriendList = array();

        foreach ($sharedOfThisAnim as $item) {
            array_push($sharedFriend,$item->shared_user_id);
        }

        for ($i=count($friendsList)-1; $i>=0; $i--){
            $friendId =  $friendsList[$i]->get("id");
            $isIn = false;
            foreach ($sharedFriend as $item) {
                if($friendId === $item){
                    $isIn = true;
                    break;
                }
            }
            if($isIn){
                array_push($sharedFriendList,$friendsList[$i]);
                array_splice($friendsList,$i,1);
            }
        }

        FileManager::loadFileFromField($friendsList,["profile_img"]);
        FileManager::loadFileFromField($sharedFriendList,["profile_img"]);

        Response::json(array("friends"=>$friendsList, "shared"=>$sharedFriendList),200);
    }

    public function share(Request $request, int $id, int $friendId){

        $conspect = Conspect::find($id);
        if($conspect){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$conspect->get("user_id")]
            ]);
        }else{
            Response::error("Nie ma takiego elementu.");
        }

        $shared = SharedConspect::create([
            "owner_user_id" => (int)$GLOBALS["user"]->id,
            "conspect_id" => $id,
            "shared_user_id" => $friendId
        ]);

        Response::json($shared,200);
    }

    public function removeShare(Request $request, int $id, int $friendId){


        $conspect = Conspect::find($id);
        if($conspect){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$conspect->get("user_id")]
            ]);
        }else{
            Response::error("Nie ma takiego elementu.");
        }

        $shared = SharedConspect::where("conspect_id" ,"=",$id)->where("shared_user_id" ,"=",$friendId)->get();

        if(isset($shared[0])){
            $shared = $shared[0];
            $shared->delete();
        }

        Response::json(true,200);
    }

}