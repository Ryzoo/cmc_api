<?php

namespace Core\Controller;

use Core\Model\File;
use Core\Model\Friend;
use Core\Model\Permission;
use Core\Model\RenderQueue;
use Core\Model\SharedAnimation;
use Core\Model\User;
use Core\System\FileManager;
use Core\System\Contract\IController;
use Core\System\Generator;
use Core\System\Request;
use Core\System\Response;
use Core\Model\Animation;
use Core\Middleware\Auth;
use Core\System\Validator;

class AnimationController implements IController{

    public function middleware(Request $request):bool {
        $canGo = Auth::check($request);
        if(!$canGo){
            $tqn = $request->get("unique");
            $user_id = $request->get("user_id");
            if(isset($tqn) || isset($user_id)){
                $currentUser = User::where("unique_token","LIKE",$tqn)->where("id","=",(int)$user_id)->get();
                $canGo = (($currentUser[0] ?? NULL) != NULL);
                global $user;
                $user = $currentUser[0];
            }
        }
        return $canGo;
    }

    public function getOne(Request $request,int $id){

        $animation = Animation::find($id);

        if($animation){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$animation->get("user_id")]
            ]);

            FileManager::loadFileFromField($animation,["main_image","url"]);
            Response::json($animation);
        }else{
            Response::error('NIe ma taakiego elementu');
        }
    }

    public function getImgId(Request $request,int $id)
    {
        $animation = Animation::find($id);
        Response::json($animation);
    }

    public function update(Request $request,int $id){
        $animation = Animation::find($id);

        if($animation){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$animation->get("user_id")]
            ]);
        }else{
            Response::error('Nie ma takiego elementu');
        }

        if($animation){
            $updateArray = [
                "name" => $request->get("name"),
                "folder_id" => $request->get("folder"),
                "user_id" => $GLOBALS["user"]->get("id"),
                "path_field" => $request->get("pathField"),
                "frame_data" => json_encode($request->get("frameData")),
                "object_in_animation" => json_encode($request->get("objectInAnimation")),
                "watermark" => $request->get("watermark") ? json_encode($request->get("watermark")) : '',
                "description" => $request->get("description") ? $request->get("description") : "",
                "equipment" => json_encode($request->get("equipment") ? $request->get("equipment") : ""),
                "age_category" => $request->get("age_category") ? $request->get("age_category") : "",
                "game_field" => $request->get("game_field") ? $request->get("game_field") : "",
                "tips" => $request->get("tips") ? $request->get("tips") : "",
                "date_add" => (new \DateTime())->format("Y-m-d")
            ];

            if(!$request->get("onlyData")){
                $newFile = FileManager::putFile($request->get("first_img"),["image/jpeg","image/png"],"animation/".$animation->get("id"));
                $oldFile = \Core\System\File::getById($animation->get("main_image"));
                $oldFile->delete();
                $updateArray["main_image"] = $newFile->getId();

                // add empty video
                $newFile = FileManager::putFile("data:video/mp4;base64,",["video/mp4"],"animation/".$animation->get("id"));
                $oldFile = \Core\System\File::getById($animation->get("url"));
                $oldFile->delete();
                $updateArray["url"] = $newFile->getId();
            }

            $animation->update($updateArray);

            if(!$request->get("onlyData")){
                Animation::render((int)$animation->get("id"));
            }

            Response::json(true,200);
        }
        Response::error("Brak animacji",404);
    }

    public function copy(Request $request, int $id)
    {
        $anim = Animation::find($id);

        if($anim){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$anim->get("user_id")]
            ]);


            $updateArray = [
                "name" => $anim->get("name").' (kopia)',
                "folder_id" => $anim->get("folder_id"),
                "user_id" => $GLOBALS["user"]->get("id"),
                "path_field" => $anim->get("path_field"),
                "watermark" => $anim->get("watermark"),
                "frame_data" => $anim->get("frame_data"),
                "object_in_animation" => $anim->get("object_in_animation"),
                "description" => $anim->get("description"),
                "game_field" => $anim->get("game_field"),
                "equipment" => $anim->get("equipment"),
                "age_category" => $anim->get("age_category"),
                "tips" => $anim->get("tips"),
                "date_add" => (new \DateTime())->format("Y-m-d"),
            ];

            $mainFile = \Core\System\File::getById($anim->get('main_image'))->copy();
            $updateArray["main_image"] =  $mainFile->getId();

            $move = \Core\System\File::getById($anim->get('main_image'))->copy();
            $updateArray["url"] = $move->getId();

            $animation = Animation::create($updateArray);

            $mainFile->move("animation/".$animation->get("id"));
            $move->move("animation/".$animation->get("id"));

            Response::json(true);

        }else{
            Response::error("Niestety projekt do skopiowania nie został odnaleziony.");
        }
    }

    public function animationsLast(Request $request){
        $animations = Animation::where("user_id","=",(int)$GLOBALS["user"]->id)->orderBy('date_add', true)->get(6);
        foreach ($animations as $anim){
            $anim->url = FileManager::getUserFileUrl($anim->url.".mp4",null,"animation/".$anim->id);
            $anim->main_image = FileManager::getUserFileUrl($anim->main_image.".jpeg",null,"animation/".$anim->id);
        }
        Response::json($animations,200);
    }

    public function saveRenderImage(Request $request, int $id)
    {
        $anim = Animation::find($id);

        if($anim){
            $file = \Core\System\File::getById($anim->main_image);

            $dir = $file->getPath(true);

            if(file_exists($dir.'/last.jpeg')){
                $file->delete();
                $fileName = Generator::generateString(25);
                $file = \Core\System\File::create($fileName, "jpeg", file_get_contents($dir.'/last.jpeg'), "animation/{$id}", $GLOBALS['user']);
                $anim->update([
                    'main_image' => $file->getId()
                ]);
            }

            $renderQueue = RenderQueue::where("animation_id","=",$id)->where("is_render","=","TRUE")->get();
            if(isset($renderQueue[0])){
                var_dump($renderQueue[0]);
                $renderQueue[0]->update([
                    "is_end" => 1,
                    "is_render" => 0
                ]);
                die();
            }
        }
    }

    public function getAll(Request $request){
        $animations = Animation::where("user_id","=",(int)$GLOBALS["user"]->id)->orderBy('date_add', true)->get($request);
        FileManager::loadFileFromField($animations,["main_image","url"]);
        Response::json($animations,200);
    }

    public function getAllShared(Request $request){
        $sharedList = SharedAnimation::where("shared_user_id", "=",(int)$GLOBALS["user"]->get("id"))->get();
        $sharedString = "";

        foreach ($sharedList as $key => $sh){
            if($key === 0){
                $sharedString .= "'".$sh->get("animation_id")."'";
            }else $sharedString .= ", "."'".$sh->get("animation_id")."'";
        }

        $animations = [];

        if(strlen($sharedString) > 0){
            $animations = Animation::where("id","IN","(".$sharedString.")")->orderBy('date_add', true)->get($request);
            FileManager::loadFileFromField($animations,["main_image","url"]);
        }

        Response::json($animations,200);
    }

    public function delete(Request $request, int $id){

        $anim = Animation::find($id);
        if($anim){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$anim->get("user_id")]
            ]);
        }else{
            Response::error('Nie ma takiego elementu');
        }

        Animation::remove($id);
        FileManager::deleteUserDir("animation/".$id);
        Response::json(true,200);
    }

    public function download(Request $request,int $imgId){
        Response::file($imgId);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("name")->length(1,255)
            ->get("folder")->isNotNull()
            ->get("frameData")->isNotNull()
            ->get("objectInAnimation")->isNotNull()
            ->get("pathField")->length(5);

        $updateArray = [
            "name" => $request->get("name"),
            "folder_id" => $request->get("folder"),
            "user_id" => $GLOBALS["user"]->get("id"),
            "path_field" => $request->get("pathField"),
            "watermark" => $request->get("watermark") ? json_encode($request->get("watermark")) : '',
            "frame_data" => json_encode($request->get("frameData")),
            "object_in_animation" => json_encode($request->get("objectInAnimation")),
            "description" => $request->get("description") ? $request->get("description") : "",
            "game_field" => $request->get("game_field") ? $request->get("game_field") : "",
            "equipment" => json_encode($request->get("equipment") ? $request->get("equipment") : ""),
            "age_category" => $request->get("age_category") ? $request->get("age_category") : "",
            "tips" => $request->get("tips") ? $request->get("tips") : "",
            "date_add" => (new \DateTime())->format("Y-m-d"),
        ];

        $imgFile = FileManager::putFile($request->get("first_img"),["image/jpeg","image/png"]);
        $updateArray["main_image"] = $imgFile->getId();

        // add empty video
        $videFile = FileManager::putFile("data:video/mp4;base64,",["video/mp4"]);
        $updateArray["url"] = $videFile->getId();

        $animation = Animation::create($updateArray);

        $imgFile->move("animation/".$animation->get("id"));
        $videFile->move("animation/".$animation->get("id"));

        Animation::render((int)$animation->get("id"));

        Response::json($animation,200);
    }

    public function getSharedUnSharedList(Request $request, int $id){

        $anim = Animation::find($id);
        if($anim){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$anim->get("user_id")]
            ]);
        }else{
            Response::error('Nie ma takiego elementu');
        }

        $sharedOfThisAnim = SharedAnimation::where("animation_id","=",$id)->get();
        $friendsList = Friend::getUserFriends();
        $sharedFriend = array();
        $sharedFriendList = array();

        foreach ($sharedOfThisAnim as $item) {
            array_push($sharedFriend,$item->get("shared_user_id"));
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

        $anim = Animation::find($id);
        if($anim){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$anim->get("user_id")]
            ]);
        }else{
            Response::error('Nie ma takiego elementu');
        }

        $shared = SharedAnimation::create([
            "owner_user_id" => (int)$GLOBALS["user"]->get("id"),
            "animation_id" =>  $id,
            "shared_user_id" => $friendId
        ]);

        Response::json($shared,200);
    }

    public function removeShare(Request $request, int $id, int $friendId){

        $anim = Animation::find($id);
        if($anim){
            Permission::check([
                ["full_license_access",null],
                ["simple-owner",$anim->get("user_id")]
            ]);
        }else{
            Response::error('Nie ma takiego elementu');
        }

        $shared = SharedAnimation::where("animation_id" ,"=",$id)->where("shared_user_id" ,"=",$friendId)->get();

        if(isset($shared[0])){
            $shared = $shared[0];
            $shared->delete();
        }

        Response::json(true,200);
    }
}