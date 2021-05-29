<?php

namespace Core\Controller;

use Core\Models\License;
use Core\Models\Wallet;
use Core\System\EmailSender;
use Core\System\FileManager;
use Core\System\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Models\User;
use Core\Middlewares\Auth as MAuth;
use Core\System\Validator;

class Auth implements IController{

    public function middleware(Request $request):bool {
        return true;
    }

    public function login(Request $request){
        Validator::validateRequest($request)
            ->get("email")->length(3,50)
            ->get("password")->length(3,50)
            ->get("place")->length(3,50);

        $email = $request->get("email");
        $password = $request->get("password");
        $place = $request->get("place");

        $users = User::where("email","LIKE",$email)->get(1);

        if(!isset($users[0])){
            return new Response('Nie ma konta z takim adresem email.',401);
        }
        else if(password_verify($password, $users[0]->password)){

            $license = License::where("user_id","=",$users[0]->id)->get();

            if(!isset($license[0])){
                return new Response('Twoje konto jest nieaktywne lub przestarzałe.',401);
            }
            $today = new \DateTime();
            $licenseData  = null;

            switch ($place){
                case "conspect":

                    $licenseData = new \DateTime($license[0]->conspect_manager);

                    if(is_null($license[0]->conspect_manager)){
                        return new Response('Nie posiadasz licencji, aby używać tego produktu. Możesz zakupić na centrumklubu.pl',401);
                    }

                    if($today > $licenseData){
                        $dataLi = $licenseData->format('Y-m-d');
                        return new Response("Twoja licencja zakończyła się w dniu: {$dataLi}",401);
                    }

                    break;
            }

            $users[0]->login_token = $GLOBALS["generator"]->generateString(50,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_");
            $users[0]->save();

            if($users[0]->profile_img === "default"){
                $users[0]->profile_img = "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg";
            }else{
                $users[0]->profile_img = FileManager::getUserFileUrl($users[0]->profile_img.".jpg");
            }

            unset($users[0]->password);
            unset($users[0]->unique_token);

            return new Response($users[0]);
        } else return new Response('Zostało podane błędne hasło.',401);

    }

    public function register(Request $request){
        Validator::validateRequest($request)
            ->get("email")->length(3,50)
            ->get("firstname")->length(3,50)
            ->get("lastname")->length(3,50);

        $users = User::where("email","LIKE", $request->get("email"))->get(1);
        if(isset($users[0])){
            return new Response('Ten adres email jest już zajęty.',401);
        }else{
            $newUser = new User();

            $password = $request->get('password') ?? $GLOBALS["generator"]->generateString(6,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_");

            $newUser->email = $request->get("email");
            $newUser->firstname = $request->get("firstname");
            $newUser->lastname = $request->get("lastname");
            $newUser->profile_img = "default";
            $newUser->full_activated = false;
            $newUser->account_type = 'new';
            $newUser->password = password_hash($password,PASSWORD_DEFAULT);
            $newUser->login_token = $GLOBALS["generator"]->generateString(50,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_");
            $newUser->unique_token = $GLOBALS["generator"]->generateString(50,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_");
            $newUser->date_add = (new \DateTime())->format('Y-m-d H:i:s');
            $newUser->save();

            $newUser->profile_img = $newUser->profile_img = "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg";

            $newLicense = new License();
            $newLicense->user_id = $newUser->id;
            $newLicense->update_date = (new \DateTime())->format('Y-m-d H:i:s');
            $newLicense->save();

            $wallet = new Wallet();
            $wallet->user_id = $newUser->id;
            $wallet->status = 0;
            $wallet->save();

            $email = new EmailSender();
            $param = array(
                [
                    "name"=>"header",
                    "content"=>"Witaj ".$newUser->firstname." ".$newUser->lastname
                ],
                [
                    "name"=>"main",
                    "content"=>"Utworzyliśmy dla Ciebie nowe konto, Twoje tymczasowe hasło to: <b>".$password."</b><br/>Możesz teraz przejść do strony i zalogować się."
                ],
                [
                    "name"=>"button_url",
                    "content"=>"https://centrumklubu.pl/login"
                ],
                [
                    "name"=>"button_name",
                    "content"=>"Przejdź do strony"
                ]
            );
            $email->sendEmail($newUser->email,`${$newUser->firstname} ${$newUser->lastname}`, "Utworzyliśmy dla Ciebie nowe konto", $param);

            return new Response($newUser,200);
        }
    }

    public function checkAuth(Request $request){
        Validator::validateRequest($request)
            ->get("loginToken")->length(15,255);

        $loginToken = $request->get("loginToken");
        $user = User::where("login_token","LIKE",$loginToken)->get(1);

        if($user[0]->profile_img == "default"){
            $user[0]->profile_img = "//".$_SERVER['SERVER_NAME']."/resources/profile_default.svg";
        }else{

            $user[0]->profile_img = FileManager::getUserFileUrl($user[0]->profile_img.".jpg", $user[0]);
        }

        return new Response($user);
    }
}