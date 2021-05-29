<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 10.09.18
 * Time: 18:30
 */

namespace Core\Controller;

use Core\Models\Animation;
use Core\Models\ExtendedProfile;
use Core\Models\Friend;
use Core\Models\License;
use Core\Models\Notification;
use Core\Models\SharedAnimation;
use Core\Models\SharedConspect;
use Core\Models\User;
use Core\System\EmailSender;
use Core\System\FileManager;
use Core\System\IController;
use Core\System\QueryBuilder;
use Core\System\Request;
use Core\System\Response;
use Core\Middlewares\Auth;
use Core\System\Validator;

class ShareController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getSharedAnimationsList(Request $request, int $id){
        $sharedOfThisAnim = SharedAnimation::where("animation_id","=",$id)->get();
        $friendsList = $this->getFriendList();
        $sharedFriend = array();
        $sharedFriendList = array();

        foreach ($sharedOfThisAnim as $item) {
            array_push($sharedFriend,$item->shared_user_id);
        }

        for ($i=count($friendsList)-1; $i>=0; $i--){
            $friendId =  $friendsList[$i]->id;
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

        return new Response(array("friends"=>$friendsList, "shared"=>$sharedFriendList),200);
    }

    public function shareAnimation(Request $request, int $id, int $friendId){
        $shared = new SharedAnimation();
        $shared->owner_user_id = (int)$GLOBALS["user"]->id;
        $shared->animation_id = $id;
        $shared->shared_user_id = $friendId;
        $shared->save();

        return new Response(null,200);
    }

    public function removeSharedAnimationFrom(Request $request, int $id, int $friendId){

        $shared = SharedAnimation::where("animation_id" ,"=",$id)->where("shared_user_id" ,"=",$friendId)->get();
        if(isset($shared[0])){
            $shared = $shared[0];
            $shared->delete();
        }

        return new Response(null,200);
    }

    private function getFriendList(){
        $allFriends = QueryBuilder::select(array_merge(User::getFields(['firstname', 'lastname', 'id', 'profile_img', 'unique_token']), Friend::getFields(['date'])))
            ->from(Friend::$table)
            ->joinOn(User::$table,Friend::$table.".user2_id", User::$table.".id")
            ->where("user_id", "=", (int)$GLOBALS["user"]->id)
            ->get();

        for($i=0;$i<count($allFriends);$i++){
            $allFriends[$i] = (object) $allFriends[$i];
            if($allFriends[$i]->profile_img == "default"){
                $allFriends[$i]->profile_img= "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg";
            }else{
                $allFriends[$i]->profile_img = FileManager::getUserFileUrl($allFriends[$i]->profile_img.".jpg", $allFriends[$i]);
            }
            unset($allFriends[$i]->unique_token);
        }

        return $allFriends;
    }

    public function getSharedConspectList(Request $request, int $id){
        $sharedOfThisAnim = SharedConspect::where("conspect_id","=",$id)->get();
        $friendsList = $this->getFriendList();
        $sharedFriend = array();
        $sharedFriendList = array();

        foreach ($sharedOfThisAnim as $item) {
            array_push($sharedFriend,$item->shared_user_id);
        }

        for ($i=count($friendsList)-1; $i>=0; $i--){
            $friendId =  $friendsList[$i]->id;
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

        return new Response(array("friends"=>$friendsList, "shared"=>$sharedFriendList),200);
    }

    public function shareConspect(Request $request, int $id, int $friendId){
        $shared = new SharedConspect();
        $shared->owner_user_id = (int)$GLOBALS["user"]->id;
        $shared->conspect_id = $id;
        $shared->shared_user_id = $friendId;
        $shared->save();

        return new Response(null,200);
    }

    public function removeSharedConspectFrom(Request $request, int $id, int $friendId){

        $shared = SharedConspect::where("conspect_id" ,"=",$id)->where("shared_user_id" ,"=",$friendId)->get();
        if(isset($shared[0])){
            $shared = $shared[0];
            $shared->delete();
        }

        return new Response(null,200);
    }
}