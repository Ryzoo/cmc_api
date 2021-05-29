<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 25.07.18
 * Time: 17:43
 */

namespace Core\Controller;


use Core\Models\Friend;
use Core\Models\Invitation;
use Core\Models\Notification;
use Core\Models\User;
use Core\System\EmailSender;
use Core\System\FileManager;
use Core\System\IController;
use Core\System\QueryBuilder;
use Core\System\Request;
use Core\System\Response;
use Core\Middlewares\Auth;
use Core\System\Validator;
use DateTime;

class FriendManager implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function myFriends(Request $request){

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

        $invitations = QueryBuilder::select(array_merge(User::getFields(['firstname', 'lastname', 'id', 'profile_img', 'unique_token']), Invitation::getFields(['date'])))
            ->from(Invitation::$table)
            ->joinOn(User::$table,Invitation::$table.".user_id", User::$table.".id")
            ->where("user2_id", "=", (int)$GLOBALS["user"]->id)
            ->get();

        for($i=0;$i<count($invitations);$i++){
            $invitations[$i] = (object) $invitations[$i];
            if($invitations[$i]->profile_img == "default"){
                $invitations[$i]->profile_img= "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg";
            }else{
                $invitations[$i]->profile_img = FileManager::getUserFileUrl($invitations[$i]->profile_img.".jpg", $invitations[$i]);
            }
            unset($invitations[$i]->unique_token);
        }

        return new Response(array("friends"=>$allFriends, "invite"=>$invitations),200);
    }

    public function delete(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNumber();

        $friends = Friend::where("user2_id" , "=", (int)$request->get("id"))->where("user_id" , "=", (int)$GLOBALS["user"]->id)->get();

        if(isset($friends)){
            if(is_array($friends)){
                foreach ($friends as $friend){
                    $friend->delete();
                }
            }else{
                $friends->delete();
            }
        }

        $friends = Friend::where("user_id" , "=", (int)$request->get("id"))->where("user2_id" , "=", (int)$GLOBALS["user"]->id)->get();

        if(isset($friends)){
            if(is_array($friends)){
                foreach ($friends as $friend){
                    $friend->delete();
                }
            }else{
                $friends->delete();
            }
        }

        return new Response(true,200);
    }

    public function inviteReply(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNumber()
            ->get("status")->isNotNull();

        if($request->get("status")){

            $friend = new Friend();
            $friend->user_id = $request->get("id");
            $friend->user2_id = (int)$GLOBALS["user"]->id;
            $friend->date = (new DateTime())->format('Y-m-d H:i:s');
            $friend->save();

            $friend = new Friend();
            $friend->user2_id = $request->get("id");
            $friend->user_id = (int)$GLOBALS["user"]->id;
            $friend->date = (new DateTime())->format('Y-m-d H:i:s');
            $friend->save();

            $notification = new Notification();
            $notification->user_id = $request->get("id");
            $notification->title = "Zaakceptowano Twoje zaproszenie";
            $notification->content = "Użytkownik: " . $GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname ." zaakceptował Twoje zaproszenie do znajomych.";
            $notification->icon = null;
            $notification->isRead = 1;
            $notification->date = (new \DateTime())->format('Y-m-d H:i:s');
            $notification->url = null;
            $notification->save();
        }else{
            $notification = new Notification();
            $notification->user_id = $request->get("id");
            $notification->title = "Odrzucono Twoje zaproszenie";
            $notification->content = "Użytkownik: " . $GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname ." odrzucił Twoje zaproszenie do znajomych.";
            $notification->icon = null;
            $notification->isRead = 1;
            $notification->date = (new \DateTime())->format('Y-m-d H:i:s');
            $notification->url = null;
            $notification->save();
        }

        $invites = Invitation::where("user2_id","=",(int)$GLOBALS["user"]->id)->where("user_id","=",(int)$request->get("id"))->get();

        foreach ($invites as $invite){
            $invite->delete();
        }

        if($request->get("status")){
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

            return new Response($allFriends,200);
        }else{
            return new Response(true,200);
        }
    }

    public function emailInvite(Request $request){
        Validator::validateRequest($request)
            ->get("email")->length(5,255);

        $email = new EmailSender();
        $param = array(
            [
                "name"=>"header",
                "content"=>"Dzień dobnry"
            ],
            [
                "name"=>"main",
                "content"=>"Jako zespół Club Management Center zapraszamy Cię w imieniu  <b>".$GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname ."</b> do skorzystania z naszej aplikacji.</br> Więcej informacji możesz uzyskać klikając przycisk poniżej."
            ],
            [
                "name"=>"button_url",
                "content"=>"https://centrumklubu.pl"
            ],
            [
                "name"=>"button_name",
                "content"=>"Przejdź do strony centrumklubu"
            ]
        );
        $email->sendEmail($request->get("email"),``, $GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname." zaprasza Cię do Centrum Klubu", $param);

        return new Response(true,200);
    }

    public function invite(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNumber();

        $user = User::find($request->get("id"));
        $friends = Friend::where("user_id","=",(int)$GLOBALS["user"]->id)->where("user2_id","=",(int)$request->get("id"))->get();
        $invites = Invitation::where("user_id","=",(int)$GLOBALS["user"]->id)->where("user2_id","=",(int)$request->get("id"))->get();
        $invites2 = Invitation::where("user2_id","=",(int)$GLOBALS["user"]->id)->where("user_id","=",(int)$request->get("id"))->get();

        if(isset($user->unique_token) && count($friends) == 0 && count($invites) == 0 && count($invites2) == 0){
            $friend = new Invitation();
            $friend->user_id = (int)$GLOBALS["user"]->id;
            $friend->user2_id = $request->get("id");
            $friend->date = (new DateTime())->format('Y-m-d H:i:s');
            $friend->save();

            $notification = new Notification();
            $notification->user_id = $request->get("id");
            $notification->title = "Nowe zaproszenie do znajomych";
            $notification->content = "Masz nowe zaproszenie do znajomych od: " . $GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname .". Przejdź do zakłądki znajomi i odpowiedz.";
            $notification->icon = null;
            $notification->isRead = 1;
            $notification->date = (new \DateTime())->format('Y-m-d H:i:s');
            $notification->url = "https://app.centrumklubu.pl/#/friends";
            $notification->save();

            $email = new EmailSender();
            $param = array(
                [
                    "name"=>"header",
                    "content"=>"Witaj ".$user->firstname . " " . $user->lastname
                ],
                [
                    "name"=>"main",
                    "content"=>"Użytkownik <b>".$GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname ."</b> zaprasza Cię do znajomych.</br> Możesz zaakceptować lub odrzucić dane zaproszenie na stronie, aby szybko przejść do aplikacji skorzystaj z przycisku poniżej."
                ],
                [
                    "name"=>"button_url",
                    "content"=>"https://app.centrumklubu.pl"
                ],
                [
                    "name"=>"button_name",
                    "content"=>"Przejdź do strony"
                ]
            );
            $email->sendEmail($user->email, $user->firstname . " " . $user->lastname, "Nowe zaproszenie do znajomych", $param);

            return new Response(true,200);
        }

        return new Response(false,406);
    }

    public function find(Request $request){
        Validator::validateRequest($request)
            ->get("name")->length(3,255);


        $users = QueryBuilder::select(User::getFields(['firstname', 'lastname', 'id', 'profile_img', 'unique_token']))
            ->from(User::$table)
            ->where("id", "<>",  (int)$GLOBALS["user"]->id)
            ->where("id", "NOT IN", "(SELECT user2_id FROM Friend WHERE user_id = ".((int)$GLOBALS["user"]->id.")"))
            ->where("id", "NOT IN", "(SELECT user2_id FROM Invitation WHERE user_id = ".((int)$GLOBALS["user"]->id.")"))
            ->where("id", "NOT IN", "(SELECT user_id FROM Invitation WHERE user2_id = ".((int)$GLOBALS["user"]->id.")"))
            ->where("LOWER(CONCAT(firstname, lastname, email))","LIKE", "%".str_replace(' ', '', strtolower($request->get("name"))) ."%")
            ->get();

        for($i=0;$i<count($users);$i++){
            $users[$i] = (object) $users[$i];
            if($users[$i]->profile_img == "default"){
                $users[$i]->profile_img= "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg";
            }else{
                $users[$i]->profile_img = FileManager::getUserFileUrl($users[$i]->profile_img.".jpg", $users[$i]);
            }
            unset($users[$i]->unique_token);
        }

        return new Response($users,200);
    }

}