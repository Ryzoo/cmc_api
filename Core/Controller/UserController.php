<?php

namespace Core\Controller;

use Core\Model\Animation;
use Core\Model\Conspect;
use Core\Model\Event;
use Core\Model\ExtendedProfile;
use Core\Model\Folder;
use Core\Model\Form;
use Core\Model\FormAnswer;
use Core\Model\FormField;
use Core\Model\Invitation;
use Core\Model\License;
use Core\Model\Message;
use Core\Model\Notification;
use Core\Model\Permission;
use Core\Model\Role;
use Core\Model\SharedAnimation;
use Core\Model\SharedConspect;
use Core\Model\User;
use Core\Model\UserLicense;
use Core\Model\Wallet;
use Core\Model\WalletHistory;
use Core\Model\Watermark;
use Core\System\EmailSender;
use Core\System\File;
use Core\System\FileManager;
use Core\System\Contract\IController;
use Core\System\Generator;
use Core\System\Logger;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;
use Service\GiftService;

class UserController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function activate(Request $request, int $id){

        Permission::check([
            ["simple-id",$id]
        ]);

        Validator::validateRequest($request)
            ->get("form")->isNotNull()
            ->get("role")->isNumber()
            ->get("login_token")->length(10);

        $user = User::find($id);

        if(!$user) Response::error("Nie ma takiego użytkownika");

        if($user->get("full_activated")){
            Response::error("Twoje konto jest już aktywowane.",406);
        }

        $role = Role::find($request->get('role'));
        $form = Form::find($role->get("form_id"));

        foreach ($request->get('form') as $roleField){
            $id = $roleField["id"];
            $value = $roleField["value"];

            $formAnswer = FormAnswer::where('field_id','=',$id)
                ->where('form_id','=',$form->get('id'))
                ->where('user_id','=',$user->get('id'))
                ->get();

            if(count($formAnswer) > 0){
                $formAnswer[0]->update([
                    "field_id" => $id,
                    "value" => $value,
                    "user_id" => $user->get('id'),
                    "form_id" => $form->get('id'),
                ]);
            }else{
                FormAnswer::create([
                    "field_id" => $id,
                    "value" => $value,
                    "user_id" => $user->get('id'),
                    "form_id" => $form->get('id'),
                ]);
            }
        }

        $user->update([
            "role_id" => $request->get('role'),
            "full_activated" => true
        ]);

        License::extend([3],$user,'day',14);

        $giftList = $request->get("gift");

        if($giftList && count($giftList) > 0){
            foreach ($giftList as $gift){
                GiftService::useGiftKey($gift["gift_key"]);
            }
        }

        Response::json(true,200);
    }

    public static function extendUserLicense(Request $request, int $userId, int $licenseId)
    {
        Permission::check([
            ["full_license_access",null],
            ["simple-id",$userId]
        ]);

        Validator::validateRequest($request)
            ->get("month")->length(1,3);

        $license = UserLicense::where("license_id","=",$licenseId)
            ->where("user_id","=",$userId)
            ->get();

        if(isset($license[0])){
            $license = $license[0];

            $mothPrice = explode("|",$license->license->price);

            foreach ($mothPrice as $mp){
                $month = explode("#",$mp)[0];
                $price = explode("#",$mp)[1];

                if($request->get('month') === $month){

                    $wallet = Wallet::getUserWallet();
                    $wallet->removeAmount($price,"Przedłużenie licencji {$license->license->name}");

                    License::extend([$license->license_id],$GLOBALS['user'],'month',$request->get('month'));

                    Response::json(true);
                }
            }

            Response::error("Nie mogliśmy znaleźć danego okresu czasu w podanej licencji");
        }else{
            Response::error("Nie możemy znaleźć podanej licencji");
        }

    }

    public function getUserLicense(Request $request, int $id)
    {
        Permission::check([
            ["full_license_access",null],
            ["simple-id",$id]
        ]);

        $user = $GLOBALS['user'];

        $licenses = UserLicense::where("user_id","=",$id)->get();
        $all = License::where("possibleRole","LIKE","%{$user->get("role_id")}%")->get();

        $available = [];
        $actived = [];

        foreach ($all as $item) {

            $isIn = false;
            $lic = null;
            foreach ($licenses as $license) {
                $dateNow = new \DateTime();
                $dateNow->setTime(1,1);
                $dateIt = new \DateTime($license->get('date_end'));
                $dateIt->setTime(1,1);

                if($license->get('license_id') === $item->get('id') &&  $dateIt >= $dateNow){
                    $isIn = true;
                    $lic = $license;
                    break;
                }
            }

            if($isIn && $lic){
                $actived[] = $lic;
            }else{
                $available[] = $item;
            }
        }

        Response::json([
            "actived"=>$actived,
            "available"=>$available
        ]);
    }

    public function getAll(Request $request){
        Permission::check([
            ["full_license_access",null]
        ]);
        $users = User::where("id","=","id")->get($request);
        FileManager::loadFileFromField($users,["profile_img"]);
        Response::json($users);
    }

    public function saveUser(Request $request, int $id){

        Permission::check([
            ["full_license_access",null],
            ["simple-id",$id]
        ]);

        Validator::validateRequest($request)
            ->get("firstname")->length(3, 50)
            ->get("lastname")->length(3, 50)
            ->get("email")->length(3, 50)
            ->get("role_id")->isNumber()
            ->get("roleForm")->isNotNull();


        $users = User::where("email","LIKE", $request->get("email"))->get();

        if(count($users) >= 2){
            Response::error('Ten adres email jest już zajęty.',401);
        }

        $user = User::find($id);

        $updateArray = [
            "firstname" => $request->get('firstname'),
            "lastname" => $request->get('lastname'),
            "email" => $request->get('email'),
            "role_id" => $request->get('role_id')
        ];

        $pwd = $request->get('password');

        if(isset($pwd))
            $updateArray["password"] = password_hash($request->get('password'),PASSWORD_DEFAULT);

        $img = $request->get("profile_img");


        if(isset( $img ) && strpos($img, "//".$_SERVER['SERVER_NAME']) === false){


            FileManager::validateSize($img,2);
            $newFile = FileManager::putFile($img,["image/png","image/jpeg","image/gif","image/svg+xml"]);


            if($user->get("profile_img") !== "default"){
                $oldFile = File::getById($user->get("profile_img"));
                $oldFile->delete();
            }



            $updateArray["profile_img"] = $newFile->getId();
        }


        $user->update($updateArray);


        $role = Role::find($request->get('role_id'));
        $form = Form::find($role->get("form_id"));

        foreach ($request->get('roleForm') as $roleField){
            $id = $roleField["field_id"];
            $value = $roleField["value"];

            if($value || strlen($value) > 0 ){
                $formAnswer = FormAnswer::where('field_id','=',$id)
                    ->where('form_id','=',$form->get('id'))
                    ->where('user_id','=',$user->get('id'))
                    ->get();

                if(count($formAnswer) > 0){
                    $formAnswer[0]->update([
                        "field_id" => $id,
                        "value" => $value,
                        "user_id" => $user->get('id'),
                        "form_id" => $form->get('id'),
                    ]);
                }else{
                    FormAnswer::create([
                        "field_id" => $id,
                        "value" => $value,
                        "user_id" => $user->get('id'),
                        "form_id" => $form->get('id'),
                    ]);
                }
            }
        }

        Response::json(true);
    }

    public function update(Request $request){

        Validator::validateRequest($request)
            ->get("firstname")->length(3,255)
            ->get("lastname")->length(3,255)
            ->get("email")->length(3,255);

        $users = User::where("email","LIKE", $request->get("email"))->get();

        if(count($users) >= 2){
            Response::error('Ten adres email jest już zajęty.',401);
        }

        $user = $GLOBALS["user"];

        $updateArray = [
            "firstname" => $request->get("firstname"),
            "lastname" => $request->get("lastname"),
            "email" => $request->get("email")
        ];

        $img = $request->get("profile_img");

        if(isset( $img ) && strpos($img, "//".$_SERVER['SERVER_NAME']) === false){


            FileManager::validateSize($img,2);
            $newFile = FileManager::putFile($img,["image/png","image/jpeg","image/gif","image/svg+xml"]);

            if($user->get("profile_img") !== "default"){
                $oldFile = File::getById($user->get("profile_img"));
                $oldFile->delete();
            }

            $updateArray["profile_img"] = $newFile->getId();
        }

        $user->update($updateArray);

        Response::json(true);
    }

    public function changePassword(Request $request){
        Validator::validateRequest($request)
            ->get("new")->length(5,255)
            ->get("old")->length(5,255);

        $user = $GLOBALS["user"];

        if(password_verify($request->get('old'), $user->get("password"))){

            $user->update([
                "password" => password_hash($request->get('new'),PASSWORD_DEFAULT)
            ]);

            Notification::add(
                "Zostało zmienione hasło",
                "Hasło do Twojego konta zostało zmienione. Jeśli to nie Ty wykonałeś zmianę skontaktuj się z nami jak najszybciej."
            );

            $email = new EmailSender();
            $param = array(
                [
                    "name"=>"header",
                    "content"=>"Witaj ".$GLOBALS["user"]->get("firstname") . " " . $GLOBALS["user"]->get("lastname")
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
            $email->sendEmail($GLOBALS["user"]->get("email"), $GLOBALS["user"]->get("firstname") . " " . $GLOBALS["user"]->get("lastname"), "Zmiana hasła do konta", $param);

            Response::json(true,200);
        }
        Response::error('Zostało podane błędne hasło.',401);
    }

    public function delete(Request $request, int $id)
    {
        Permission::check([
            ["full_license_access",null],
            ["simple-id",$id]
        ]);

        $user = User::remove($id);

        $wallet = Wallet::where("user_id","=",$id)->get();
        $lic = UserLicense::where("user_id","=",$id)->get();
        $sharedCons = SharedConspect::where("owner_user_id","=",$id)->get();
        $sharedAnim = SharedAnimation::where("owner_user_id","=",$id)->get();
        $notif = Notification::where("user_id","=",$id)->get();
        $message = Message::where("user_from","=",$id)->whereOr("user_to","=",$id)->get();
        $invite = Invitation::where("user_id","=",$id)->whereOr("user2_id","=",$id)->get();
        $formAns = FormAnswer::where("user_id","=",$id)->get();
        $file = \Core\Model\File::where("user_id","=",$id)->get();
        $event = Event::where("user_id","=",$id)->get();
        $conspect = Conspect::where("user_id","=",$id)->get();
        $anim = Animation::where("user_id","=",$id)->get();
        $folders = Folder::where("user_id","=",$id)->get();
        $watermarks = Watermark::where("user_id","=",$id)->get();

        foreach ($wallet as $item){
            $walletHist = WalletHistory::where("wallet_id","=",$item->id)->get();
            foreach ($walletHist as $itemIn){$itemIn->delete();}
            $item->delete();
        }
        foreach ($lic as $item){$item->delete();}
        foreach ($watermarks as $item){$item->delete();}
        foreach ($sharedCons as $item){$item->delete();}
        foreach ($sharedAnim as $item){$item->delete();}
        foreach ($message as $item){$item->delete();}
        foreach ($notif as $item){$item->delete();}
        foreach ($invite as $item){$item->delete();}
        foreach ($formAns as $item){$item->delete();}
        foreach ($event as $item){$item->delete();}
        foreach ($conspect as $item){$item->delete();}
        foreach ($file as $item){$item->delete();}
        foreach ($anim as $item){$item->delete();}
        foreach ($folders as $item){$item->delete();}

        Response::json(true);
    }

    public function addUser(Request $request)
    {
        Validator::validateRequest($request)
            ->get("firstname")->length(3, 50)
            ->get("lastname")->length(3, 50)
            ->get("email")->length(3, 50)
            ->get("password")->length(5, 20)
            ->get("role_id")->isNumber()
            ->get("roleForm")->isNotNull();

        $users = User::where("email","LIKE", $request->get("email"))->get();

        if(isset($users[0])){
            Response::error('Ten adres email jest już zajęty.',401);
        }

        $user = User::create([
            "firstname" => $request->get('firstname'),
            "lastname" => $request->get('lastname'),
            "email" => $request->get('email'),
            "password" => password_hash($request->get('password'),PASSWORD_DEFAULT),
            "role_id" => $request->get('role_id'),
            "passwordWihoutCrypt" => $request->get('password'),
            "profile_img" => "default",
            "full_activated" => false,
            "login_token" => Generator::generateString(50),
            "unique_token" => Generator::generateString(50),
            "date_add" => (new \DateTime())->format('Y-m-d H:i:s')
        ]);

        $role = Role::find($request->get('role_id'));
        $form = Form::find($role->get("form_id"));

        foreach ($request->get('roleForm') as $roleField){
            $id = $roleField["field_id"];
            $value = $roleField["value"];

            $formAnswer = FormAnswer::where('field_id','=',$id)
                ->where('form_id','=',$form->get('id'))
                ->where('user_id','=',$user->get('id'))
                ->get();

            if(count($formAnswer) > 0){
                $formAnswer[0]->update([
                    "field_id" => $id,
                    "value" => $value,
                    "user_id" => $user->get('id'),
                    "form_id" => $form->get('id'),
                ]);
            }else{
                FormAnswer::create([
                    "field_id" => $id,
                    "value" => $value,
                    "user_id" => $user->get('id'),
                    "form_id" => $form->get('id'),
                ]);
            }
        }
    }

    public function getOne(Request $request, int $id)
    {

        Permission::check([
            ["full_license_access",null],
            ["simple-id",$id]
        ]);

        $user = User::find($id);

        $role = Role::find($user->get('role_id'));
        $form = Form::find($role->get("form_id"));
        $fields = FormField::where("form_id","=",$form->get("id"))->get();

        $user->formField = [];

        foreach ($fields as $field){
            $fieldAnswer = FormAnswer::where('field_id','=',$field->get('id'))
                ->where('form_id','=',$form->get('id'))
                ->where('user_id','=',$user->get('id'))
                ->get();

            if(isset($fieldAnswer[0]))
            $user->formField[] = [
                "value" => $fieldAnswer[0]->get('value'),
                "field_id" => $field->get('id')
            ];
        }

        FileManager::loadFileFromField($user,["profile_img"]);

        Response::json($user);
    }

}