<?php

namespace Core\Model;

use Core\System\BasicModel;
use Core\System\EmailSender;
use Core\System\Response;

class User extends BasicModel
{

    public static $table = "Users";
    public static $fields = ["id", "firstname", "lastname", "email", "password", "login_token", "unique_token", "password_reset_token", "date_add", "profile_img", "full_activated", "role_id"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(50) NOT NULL",
        "varchar(50) NOT NULL",
        "varchar(50) NOT NULL",
        "varchar(255) NOT NULL",
        "varchar(255) DEFAULT NULL",
        "varchar(255) NOT NULL",
        "varchar(255) DEFAULT NULL",
        "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
        "varchar(255) NOT NULL DEFAULT 'default'",
        "tinyint(4) NOT NULL DEFAULT '0'",
        "int(11) DEFAULT NULL"
    ];
    public static $relations = [];

    public $id;
    public $firstname;
    public $lastname;
    public $email;
    protected $password;
    public $login_token;
    protected $unique_token;
    protected $password_reset_token;
    public $date_add;
    public $profile_img;
    public $full_activated;
    public $role_id;

    public static function create(array $field)
    {
        $user = parent::create($field);

        $wallet = Wallet::create([
            "user_id" => $user->id,
            "status" => 0
        ]);

        $email = new EmailSender();
        $param = array(
            [
                "name" => "header",
                "content" => "Witaj {$user->get('firstname')} {$user->get('lastname')}"
            ],
            [
                "name" => "main",
                "content" => "Utworzyliśmy dla Ciebie nowe konto, Twoje tymczasowe hasło to: <b>" . $field['passwordWihoutCrypt'] . "</b><br/>Możesz teraz przejść do strony i zalogować się."
            ],
            [
                "name" => "button_url",
                "content" => "https://centrumklubu.pl/login"
            ],
            [
                "name" => "button_name",
                "content" => "Przejdź do strony"
            ]
        );
        $email->sendEmail($user->get("email"), "{$user->get('firstname')} {$user->get('lastname')}", "Utworzyliśmy dla Ciebie nowe konto", $param);

        return $user;
    }

    public function isActived()
    {
        return $this->get("account_type") !== "new" || $this->get("full_activated") != 0;
    }

    public function getExtProfile()
    {
        return null;
    }
}