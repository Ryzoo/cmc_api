<?php
/**
 * Created by PhpStorm.
 * User: ryzoo
 * Date: 31.01.19
 * Time: 14:06
 */

namespace Service;

use Core\Model\Gift;
use Core\Model\License;
use Core\Model\Permission;
use Core\Model\User;
use Core\Model\UserGift;
use Core\System\Response;

class GiftService
{
    public static function validateGiftKey(string $key): Gift{
        $gifts = Gift::where("gift_key","LIKE", $key)->get();

        if(!$gifts || !$gifts[0]){
            Response::error("Nie ma takiego kodu.");
        }

        if($gifts[0]->get('used_count') <= 0){
            Response::error("Ten kod nie może być użyty ponownie.");
        }

        return $gifts[0];
    }

    public static function useGiftKey(string $key)
    {
        $user = $GLOBALS["user"];

        if(!$user){
            Response::error("Nie masz uprawnień.");
        }

        $userUse = UserGift::where("user_id","=",$user->get("id"))->where("gift_key","LIKE",$key)->get();

        if($userUse && count($userUse) > 0){
            Response::error("Ten kod został już wykorzystany przez Ciebie");
        }

        $gift = self::validateGiftKey($key);
        $gift->update([
            "used_count" => intval($gift->get('used_count')) - 1
        ]);

        $functionName = $gift->get('function_name');
        $functionName = explode("|",$functionName)[0];
        $arguments = [];

        UserGift::create([
            "gift_key" => $key,
            "user_id" => $user->get("id"),
            "date" => (new \DateTime())->format("Y-m-d")
        ]);

        foreach (explode("|",$gift->get('function_name')) as $key => $arg){
            if($key > 0){
                $arguments[] = $arg;
            }
        }

        self::$functionName($arguments);
    }

    public static function increaseLicense(array $arg)
    {
        $user = $GLOBALS["user"];
        $forWhat = $arg[0];
        $timeType = $arg[1];
        $howLong = $arg[2];

        License::extend([$forWhat],$user,$timeType,$howLong);
    }
}