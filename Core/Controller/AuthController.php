<?php

namespace Core\Controller;

use Core\Model\License;
use Core\System\EmailSender;
use Core\System\FileManager;
use Core\System\Generator;
use Core\System\Contract\IController;
use Core\System\Logger;
use Core\System\Request;
use Core\System\Response;
use Core\Model\User;
use Core\System\Validator;

class AuthController implements IController{

    public function middleware(Request $request):bool {
        return true;
    }

    public function login(Request $request){
        Validator::validateRequest($request)
            ->get("email")->length(3,50)
            ->get("password")->length(3,50)
            ->get("place")->length(1,255);

        $email = $request->get("email");
        $password = $request->get("password");
        $place = $request->get("place");

        $users = User::where("email","LIKE",$email)->get();


        if(!isset($users[0])){
            Logger::getInstance()->warning("Próba zalogowania na nieistniejące konto: {$email}");
            Response::error("Nie ma konta z takim adresem email.",401);
        }
        else if(password_verify($password, $users[0]->get("password"))){


            if(!License::verifyUserLicense($users[0], $place)){
                Logger::getInstance()->warning("Użytkownik {$email} próbował dostać się na domenę: {$place} bez licencji.");
                Response::error('Nie posiadasz licencji do tej aplikacji.',401);
            }

            $users[0]->update([
                "login_token" => Generator::generateString(50)
            ]);

            FileManager::loadFileFromField($users[0],["profile_img"]);

            Response::json($users[0]);
        } else {
            Logger::getInstance()->warning("Podanie złego hasła do konta: {$email}");
            Response::error('Zostało podane błędne hasło.',401);
        }

    }

    public function register(Request $request){
        Validator::validateRequest($request)
            ->get("email")->length(3,50)
            ->get("firstname")->length(3,50)
            ->get("lastname")->length(3,50);

        $users = User::where("email","LIKE", $request->get("email"))->get();
        if(isset($users[0])){
            Response::error('Ten adres email jest już zajęty.',401);
        }else{
            $password = $request->get('password') ?? Generator::generateString(6);

            $newUser = User::create([
                "passwordWihoutCrypt" => $password,
                "email" => $request->get("email"),
                "firstname" => $request->get("firstname"),
                "lastname" => $request->get("lastname"),
                "profile_img" => "default",
                "full_activated" => false,
                "account_type" => 'new',
                "password" => password_hash($password,PASSWORD_DEFAULT),
                "login_token" => Generator::generateString(50),
                "unique_token" => Generator::generateString(50),
                "date_add" => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

            Response::json($newUser,200);
        }
    }

    public function checkAuth(Request $request){
        Validator::validateRequest($request)
            ->get("loginToken")->length(15,255);

        $loginToken = $request->get("loginToken");
        $user = User::where("login_token","LIKE",$loginToken)->get();

        if(isset($user[0])){
            FileManager::loadFileFromField($user[0],["profile_img"]);
            Response::json($user[0]);
        }else{
            Response::json(null);
        }
    }

    public function resetUserPassword(Request $request){
        Validator::validateRequest($request)
            ->get('email')->isNotNull();

        $user = User::where("email","LIKE",$request->get("email"))->get();
        if(isset($user[0])){
            $user = $user[0];
            $newResetToken = Generator::generateString(32);

            $user->update([
                "password_reset_token" => $newResetToken
            ]);

            $email = new EmailSender();
            $param = array(
                [
                    "name" => "header",
                    "content" => "Witaj {$user->get('firstname')} {$user->get('lastname')}"
                ],
                [
                    "name" => "main",
                    "content" => "Poprosiłeś niedawno o zresetowanie Twojego hasła. Jeśli to nie Ty poprosiłeś o tą opcję, nie martw się i zignoruj tą wiadomość. Twoje stare hasło będzie wtedy bezpieczne."
                ],
                [
                    "name" => "button_url",
                    "content" => "https://centrumklubu.pl/change-reset-password?token={$newResetToken}"
                ],
                [
                    "name" => "button_name",
                    "content" => "Kliknij, aby ustawić hasło"
                ]
            );
            $email->sendEmail($user->get("email"), "{$user->get('firstname')} {$user->get('lastname')}", "Prośba o zresetowanie hasła", $param);

            Response::json(true);
        }else{
            Response::error("Nie ma użytkownika o podanym adresie email.");
        }
    }

    public function changeResetUserPassword(Request $request){
        Validator::validateRequest($request)
            ->get('token')->isNotNull()
            ->get('newPassword')->isNotNull()
            ->get('reNewPassword')->isNotNull();

        $user = User::where("password_reset_token","LIKE",$request->get("token"))->get();
        if(isset($user[0])){
            $user = $user[0];
            $password = $request->get("newPassword");
            $rePassword = $request->get("reNewPassword");

            if($password !== $rePassword){
                Response::error("Podane hasło nie zostało poprawnie powtórzone.");
            }

            $user->update([
                "password_reset_token" => NULL,
                "password" => password_hash($password,PASSWORD_DEFAULT),
            ]);

            $email = new EmailSender();
            $param = array(
                [
                    "name" => "header",
                    "content" => "Witaj {$user->get('firstname')} {$user->get('lastname')}"
                ],
                [
                    "name" => "main",
                    "content" => "Dokończyłeś proces zmiany hasła. Twoje nowe hasło to: {$password}. Możesz teraz bezpiecznie się logować."
                ],
                [
                    "name" => "button_url",
                    "content" => "https://centrumklubu.pl/login"
                ],
                [
                    "name" => "button_name",
                    "content" => "CENTRUMKLUBU - logowanie"
                ]
            );
            $email->sendEmail($user->get("email"), "{$user->get('firstname')} {$user->get('lastname')}", "Ukończenie resetowania hasła", $param);

            Response::json(true);
        }else{
            Response::error("Token do resetowania hasła jest niepoprawny. Skontaktuj sie z nami jeśli nie możesz zresetować hasła.");
        }

    }
}