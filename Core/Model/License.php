<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.09.18
 * Time: 06:58
 */

namespace Core\Model;

use Core\System\BasicModel;
use Core\System\Response;

class License extends BasicModel
{
    public static $table = "License";
    public static $fields = ["id", "name","price", "description","powers", "possibleRole", "possiblePlace", "siteUrl"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(255) NOT NULL",
        "varchar(255) NOT NULL",
        "text NOT NULL",
        "text NOT NULL",
        "varchar(255) NOT NULL",
        "varchar(255) NOT NULL",
        "varchar(255) NOT NULL",
    ];
    public static $relations = [];

    public $id;
    public $name;
    public $price;
    public $description;
    public $powers;
    public $possibleRole;
    public $possiblePlace;
    public $siteUrl;

    public static function extend(array $productsId,?User $user = null,string $intervalType = 'day', int $interval = 14)
    {
        if(!$user){
            $user = $GLOBALS['user'];
        }

        foreach ($productsId as $product) {
            $lic = UserLicense::where("user_id","=",$user->get('id'))
                ->where("license_id","=",$product)
                ->get();

            if(isset($lic[0])){
                $currentDate = new \DateTime($lic[0]->get("date_end"));
                $currentDate->modify("+".$interval." ".$intervalType);

                $lic[0]->update([
                    "user_id" => $user->get('id'),
                    "license_id" => $product,
                    "date_end" => $currentDate->format("Y-m-d H:i:s")
                ]);
            }else{
                $currentDate = new \DateTime();
                $currentDate->modify("+".$interval." ".$intervalType);

                UserLicense::create([
                    "user_id" => $user->get('id'),
                    "license_id" => $product,
                    "date_end" => $currentDate->format("Y-m-d H:i:s")
                ]);
            }
        }
    }

    public static function verifyUserLicense(?User $user = null, $place)
    {
        if(!$user){
            $user = $GLOBALS['user'];
        }

        if($place === 'centrumklubu') return true;

        if(!$user->get('full_activated')) return false;

        if(Permission::userHaveByName("full_license_access",$user)){
            return true;
        }

        $userLicense = UserLicense::where("user_id","=",$user->get("id"))->get();
        $serverName = $place;

        foreach ($userLicense as $license) {

            $dateNow = new \DateTime();
            $dateNow->setTime(1,1);
            $dateIt = new \DateTime($license->get('date_end'));
            $dateIt->setTime(1,1);

            if( $dateIt < $dateNow){
               continue;
            }

            $license = License::find($license->get("license_id"));
            if($license){
                $license = explode("|",$license->get("possiblePlace"));

                foreach($license as $licPlace) {
                    if($licPlace === "*") return true;
                    if(strpos($licPlace,$serverName) !== false) return true;
                }
            }
        }

        return false;
    }
}