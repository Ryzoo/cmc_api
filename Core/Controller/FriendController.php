<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 25.07.18
 * Time: 17:43
 */

namespace Core\Controller;


use Core\Model\Friend;
use Core\Model\Invitation;
use Core\Model\Notification;
use Core\Model\User;
use Core\System\EmailSender;
use Core\System\FileManager;
use Core\System\Contract\IController;
use Core\System\QueryBuilder;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;
use DateTime;

class FriendController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getAll(Request $request){

        $allFriends = Friend::getUserFriends();
        $invitations = Friend::getUserFriendsInvite();

        FileManager::loadFileFromField($allFriends,["profile_img"]);
        FileManager::loadFileFromField($invitations,["profile_img"]);

        Response::json(array("friends"=>$allFriends, "invite"=>$invitations),200);
    }

    public function delete(Request $request, int $id){
        $friends = Friend::where("user2_id" , "=", $id)->where("user_id" , "=", (int)$GLOBALS["user"]->get('id'))->get();

        if($friends)
        foreach ($friends as $friend){
            $friend->delete();
        }

        $friends = Friend::where("user_id" , "=", $id)->where("user2_id" , "=", (int)$GLOBALS["user"]->get("id"))->get();

        if($friends)
        foreach ($friends as $friend){
            $friend->delete();
        }

        Response::json(true,200);
    }

    public function inviteReply(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNotNull()
            ->get("status")->isNotNull();

        if($request->get("status")){
            Friend::addFriend($request->get("id"),(int)$GLOBALS["user"]->get("id"));
            Notification::create([
                "user_id" => $request->get("id"),
                "title" => "Zaakceptowano Twoje zaproszenie",
                "content" => "Użytkownik: " . $GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname ." zaakceptował Twoje zaproszenie do znajomych.",
                "icon" => null,
                "isRead" => FALSE,
                "date" => (new \DateTime())->format('Y-m-d H:i:s'),
                "url" => null
            ]);
        }else{
            Notification::create([
                "user_id" => $request->get("id"),
                "title" => "Odrzucono Twoje zaproszenie",
                "content" => "Użytkownik: " . $GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname ." odrzucił Twoje zaproszenie do znajomych.",
                "icon" => null,
                "isRead" => FALSE,
                "date" => (new \DateTime())->format('Y-m-d H:i:s'),
                "url" => null
            ]);
        }

        $invites = Invitation::where("user2_id","=",(int)$GLOBALS["user"]->get("id"))->where("user_id","=",(int)$request->get("id"))->get();

        if($invites)
        foreach ($invites as $invite){
            $invite->delete();
        }

        if($request->get("status")){
            $allFriends = Friend::getUserFriends();

            FileManager::loadFileFromField($allFriends,["profile_img"]);

            Response::json($allFriends,200);
        }else{
            Response::json(true,200);
        }
    }

    public function emailInvite(Request $request){
        Validator::validateRequest($request)
            ->get("email")->length(5,255);


        $user = User::where("email","LIKE",$request->get("email"))->get();
        if($user[0]){
            Response::error("Użytkownik o podanym adresie korzysta już z platformy");
        }

        $email = new EmailSender();
        $param = array(
            [
                "name"=>"header",
                "content"=>"Dzień dobnry"
            ],
            [
                "name"=>"main",
                "content"=>"Jako zespół Club Management Center zapraszamy Cię w imieniu  <b>".$GLOBALS["user"]->get("firstname") . " " . $GLOBALS["user"]->get("lastname") ."</b> do skorzystania z naszej aplikacji.</br> Więcej informacji możesz uzyskać klikając przycisk poniżej."
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
        $email->sendEmail($request->get("email"),``, $GLOBALS["user"]->get("firstname") . " " . $GLOBALS["user"]->get("lastname")." zaprasza Cię do Centrum Klubu", $param);

        Response::json(true,200);
    }

    public function invite(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNumber();

        $user = User::find($request->get("id"));
        $friends = Friend::where("user_id","=",(int)$GLOBALS["user"]->id)->where("user2_id","=",(int)$request->get("id"))->get();
        $invites = Invitation::where("user_id","=",(int)$GLOBALS["user"]->id)->where("user2_id","=",(int)$request->get("id"))->get();
        $invites2 = Invitation::where("user2_id","=",(int)$GLOBALS["user"]->id)->where("user_id","=",(int)$request->get("id"))->get();

        if(isset($user) && count($friends) == 0 && count($invites) == 0 && count($invites2) == 0){

            Invitation::create([
                "user_id" => (int)$GLOBALS["user"]->get("id"),
                "user2_id" => $request->get("id"),
                "date" => (new DateTime())->format('Y-m-d H:i:s')
            ]);

            Notification::create([
                "user_id" => $request->get("id"),
                "title" => "Nowe zaproszenie do znajomych",
                "content" => "Masz nowe zaproszenie do znajomych od: " . $GLOBALS["user"]->get("firstname") . " " . $GLOBALS["user"]->get("lastname") .". Przejdź do zakłądki znajomi i odpowiedz.",
                "icon" => null,
                "isRead" => FALSE,
                "date" => (new \DateTime())->format('Y-m-d H:i:s'),
                "url" => "https://app.centrumklubu.pl/#/friends"
            ]);

            $email = new EmailSender();
            $param = array(
                [
                    "name"=>"header",
                    "content"=>"Witaj ".$user->get("firstname") . " " . $user->get("lastname")
                ],
                [
                    "name"=>"main",
                    "content"=>"Użytkownik <b>".$GLOBALS["user"]->get("firstname") . " " . $GLOBALS["user"]->get("lastname") ."</b> zaprasza Cię do znajomych.</br> Możesz zaakceptować lub odrzucić dane zaproszenie na stronie, aby szybko przejść do aplikacji skorzystaj z przycisku poniżej."
                ],
                [
                    "name"=>"button_url",
                    "content"=>"https://app.centrumklubu.pl/#/friends"
                ],
                [
                    "name"=>"button_name",
                    "content"=>"Przejdź do strony"
                ]
            );
            $email->sendEmail($user->get("email"), $user->get("firstname") . " " . $user->get("lastname"), "Nowe zaproszenie do znajomych", $param);

            Response::json(true,200);
        }

        Response::json(false,406);
    }

    public function find(Request $request){

        $users = QueryBuilder::select(User::getFields(['id']))
            ->from(User::$table)
            ->where("id", "<>",  (int)$GLOBALS["user"]->get('id'))
            ->where("id", "NOT IN", "(SELECT user2_id FROM Friend WHERE user_id = ".((int)$GLOBALS["user"]->get('id').")"))
            ->where("id", "NOT IN", "(SELECT user2_id FROM Invitation WHERE user_id = ".((int)$GLOBALS["user"]->get('id').")"))
            ->where("id", "NOT IN", "(SELECT user_id FROM Invitation WHERE user2_id = ".((int)$GLOBALS["user"]->get('id').")"))->get();

        $userArray = [];
        foreach($users as $user){
            $userArray[] = $user["id"];
        }

        $userString = join(",",$userArray);

        $users = User::where("id","IN","({$userString})")->get($request);

        FileManager::loadFileFromField($users,["profile_img"]);

        Response::json($users,200);
    }

}