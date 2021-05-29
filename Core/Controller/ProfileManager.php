<?php

namespace Core\Controller;

use Core\Models\ExtendedProfile;
use Core\Models\License;
use Core\Models\Notification;
use Core\Models\User;
use Core\System\EmailSender;
use Core\System\FileManager;
use Core\System\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Middlewares\Auth;
use Core\System\Validator;

class ProfileManager implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function activate(Request $request){
        Validator::validateRequest($request)
            ->get("user_id")->length(1,11)
            ->get("type")->length(3,50)
            ->get("form")->isNotNull()
            ->get("license")->isNotNull();

        $form = $request->get('form');
        $user = User::find($request->get('user_id'));

        if($user->account_type !== "new" || $user->full_activated != 0){
            return new Response("Twoje konto jest już aktywowane.",406);
        }

        switch ($request->get('type')){
            case "coach":
                $this->validateFormField($form,["license"]);

                Validator::validateElement(["coach_license"=>$form["license"]["value"]])
                    ->get("coach_license")->length(1,50);

                $extProfile = new ExtendedProfile\Coach();
                $extProfile->user_id = $user->id;
                $extProfile->coach_license =$form["license"]["value"];
                $extProfile->save();

                LicenseController::addFreeLicenseToUser($user,$request->get('license'));
                break;
            default:
                return new Response("Błędny typ konta",406);
        }

        $user->account_type = $request->get('type');
        $user->full_activated = true;
        $user->save();

        return new Response(true,200);
    }

    public function getFullData(Request $request){
        $extProfile = null;

        switch ($GLOBALS["user"]->account_type){
            case "coach":
                $extProfile = ExtendedProfile\Coach::where("user_id","=",$GLOBALS["user"]->id)->get();
                $extProfile = isset($extProfile[0])?$extProfile[0]:null;
                break;
            default:
                return new Response("Błędny typ konta",406);
        }

        if($GLOBALS["user"]->profile_img == "default"){
            $GLOBALS["user"]->profile_img = "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg";
        }else{
            $GLOBALS["user"]->profile_img = FileManager::getUserFileUrl($GLOBALS["user"]->profile_img.".jpg", $GLOBALS["user"]);
        }

        return new Response([
            "base" => $GLOBALS["user"],
            "full" => $extProfile
        ],200);
    }

    private function validateFormField($form,$listOfField){
        foreach ($listOfField as $field) {
            if(!isset($form[$field]) || !isset($form[$field]['value']) || strlen($form[$field]['value']) <= 0){
                return new Response("W formularzu nie sprecyzowano odpowiednich pól.",406);
            }
        }
    }

    public function saveFullData(Request $request){
        $user = $GLOBALS["user"];
        $data = $request->get('data');

        switch ($user->account_type){
            case "coach":
                Validator::validateElement(["coach_license"=>$data["coach_license"]])
                    ->get("coach_license")->length(1,50);

                $extProfile = ExtendedProfile\Coach::where("user_id","=",$user->id)->get();
                $extProfile[0]->coach_license = $data["coach_license"];
                $extProfile[0]->save();

                break;
            default:
                return new Response("Błędny typ konta",406);
        }
        return new Response(true,200);
    }

    public function save(Request $request){
        Validator::validateRequest($request)
            ->get("firstname")->length(3,255)
            ->get("lastname")->length(3,255)
            ->get("email")->length(3,255);

        $user = $GLOBALS["user"];

        $user->firstname = $request->get("firstname");
        $user->lastname = $request->get("lastname");
        $user->email = $request->get("email");

        $img = $request->get("profile_img");

        if(isset( $img ) && $img !== $user->profile_img && $img !== "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg" && strlen($img) > 10){

            if($user->profile_img !== "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg") {
                FileManager::deleteUserFile($user->profile_img.".jpg");
            }

            $user->profile_img = $GLOBALS["generator"]->generateString(25,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
            FileManager::putImgFromData($request->get('profile_img'),$user->profile_img.".jpg");
        }

        $user->save();

        return new Response(null,200);
    }

    public function changePassword(Request $request){
        Validator::validateRequest($request)
            ->get("new")->length(5,255)
            ->get("old")->length(5,255);

        $user = $GLOBALS["user"];

        if(password_verify($request->get('old'), $user->password)){
            $user->password = password_hash($request->get('new'),PASSWORD_DEFAULT);
            $user->save();

            $notification = new Notification();
            $notification->user_id = $GLOBALS["user"]->id;
            $notification->title = "Zostało zmienione hasło";
            $notification->content = "Hasło do Twojego konta zostało zmienione. Jeśli to nie Ty wykonałeś zmianę skontaktuj się z nami jak najszybciej.";
            $notification->icon = null;
            $notification->isRead = 1;
            $notification->date = (new \DateTime())->format('Y-m-d H:i:s');
            $notification->url = "https://centrumklubu.pl/contact";
            $notification->save();

            $email = new EmailSender();
            $param = array(
                [
                    "name"=>"header",
                    "content"=>"Witaj ".$GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname
                ],
                [
                    "name"=>"main",
                    "content"=>"Twoje hasło zostało właśnie zmienione. Jeśli nie Ty wykonałeś tą zmianę skontaktuj się z nami jak najszybciej. Możesz toz robić używając przycisku poniżej."
                ],
                [
                    "name"=>"button_url",
                    "content"=>"https://centrumklubu.pl/contact"
                ],
                [
                    "name"=>"button_name",
                    "content"=>"Do strony kontaktowej"
                ]
            );
            $email->sendEmail($GLOBALS["user"]->email, $GLOBALS["user"]->firstname . " " . $GLOBALS["user"]->lastname, "Zmiana hasła do konta", $param);

            return new Response(true,200);
        }
        return new Response('Zostało podane błędne hasło.',401);
    }

}