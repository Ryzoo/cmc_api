<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 01.09.18
 * Time: 04:30
 */

namespace Core\Model\ExtendedProfile;


use Core\Model\User;
use Core\System\BasicModel;
use Core\System\Response;
use Core\System\Validator;

class Coach extends BasicModel
{
    protected static $table = "Extended_profile_coach";
    protected static $fields = ["id", "user_id", "coach_license"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "varchar(50) COLLATE utf8_polish_ci NOT NULL"
    ];
    protected static $relations = [];

    public $id;
    public $user_id;
    public $coach_license;

    public static function extend($form, ?User $user = null): Coach
    {
        // sprawedza czy podane pola istnieja w formie
        Validator::validateFormField($form, ["license"]);

        // sprawedza czy te pola posiadaja odpowiednie wartosci
        Validator::validateElement([
            "coach_license" => $form["license"]["value"]
        ])
            ->get("coach_license")->length(1, 50);


        $extProfile = Coach::create([
            "user_id" => $user->get("id"),
            "coach_license" => $form["license"]["value"]
        ]);

        return $extProfile;
    }

    public function updateData($form)
    {
        Validator::validateFormField($form, ["license"]);
        Validator::validateElement([
            "coach_license" => $form["license"]["value"]
        ])
            ->get("coach_license")->length(1, 50);

        Coach::update([
            "coach_license" => $form["license"]["value"]
        ]);
    }

}