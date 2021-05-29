<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 10.07.18
 * Time: 00:16
 */

namespace Core\Controller;

use Core\Model\Notification;
use Core\Model\User;
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;

class NotificationController implements IController
{
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

    public function getAll(Request $request){
        $notification = Notification::where("user_id","=",(int)$GLOBALS["user"]->get("id"))->get($request);
        Response::json($notification,200);
    }

    public function setAsRead(Request $request, int $id){

        $notification = Notification::find($id);

        if($notification){
            $notification->update([
                "isRead" => 1
            ]);
        }else{
            Response::error("Nie znaleziono obiektu do edycji.",404);
        }

        Response::json(true,200);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("title")->length(1,150)
            ->get("content")->length(1,255);

        $user_id = $request->get("user_id");
        if(!$user_id){
            $user_id = $GLOBALS["user"]->get("id");
        }

        $notification = Notification::create([
            "user_id" => $user_id,
            "title" => $request->get("title"),
            "content" => $request->get("content"),
            "icon" => $request->get("icon")?$request->get("icon"):null,
            "isRead" => 0,
            "date" => (new \DateTime())->format('Y-m-d H:i:s'),
            "url" => $request->get("url")?$request->get("url"):null
        ]);

        Response::json($notification,200);
    }

    public function delete(Request $request, int $id){
        Notification::delete($id);
        Response::json(true,200);
    }
}