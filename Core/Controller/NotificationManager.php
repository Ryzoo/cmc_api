<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 10.07.18
 * Time: 00:16
 */

namespace Core\Controller;

use Core\Models\Notification;
use Core\System\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Middlewares\Auth;
use Core\System\Validator;

class NotificationManager implements IController
{
    public function middleware(Request $request):bool {

        $canGo = Auth::check($request);
        if(!$canGo){
            $tqn = $request->get("unique");
            $user_id = $request->get("user_id");
            if(!isset($tqn) || !isset($user_id)){
                $currentUser = User::where("unique_token","LIKE",$tqn)->where("id","=",(int)$user_id)->get(1);
                $canGo = (($currentUser[0] ?? NULL) != NULL);
            }
        }
        return $canGo;
    }

    public function last(Request $request){
        $notification = Notification::where("user_id","=",(int)$GLOBALS["user"]->id)->where("isRead","=",(int)1)->orderBy('date', true)->get(5);
        return new Response($notification,200);
    }

    public function setAsRead(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNumber();

        $notification = Notification::find($request->get('id'));
        $notification->isRead = 0;
        $notification->save();

        return new Response(true,200);
    }

    public function read(Request $request){
        $notification = Notification::where("user_id","=",(int)$GLOBALS["user"]->id)->where("isRead","=",(int)0)->orderBy('date', true)->get(50);
        return new Response($notification,200);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("user_id")->isNumber()
            ->get("title")->length(1,150)
            ->get("content")->length(1,255);

        $notification = new Notification();
        $notification->user_id = $request->get("user_id");
        $notification->title = $request->get("title");
        $notification->content = $request->get("content");
        $notification->icon = $request->get("icon")?$request->get("icon"):null;
        $notification->isRead = 1;
        $notification->date = (new \DateTime())->format('Y-m-d H:i:s');
        $notification->url = $request->get("url")?$request->get("url"):null;
        $notification->save();

        return new Response(true,200);
    }
}