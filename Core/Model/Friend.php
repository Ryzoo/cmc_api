<?php

namespace Core\Model;

use Core\System\BasicModel;
use Core\System\QueryBuilder;
use DateTime;

class Friend extends BasicModel
{
    public static $table = "Friend";
    public static $fields = ["id", "user_id", "user2_id", "date"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
        "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ];
    public static $relations = [];

    public $id;
    public $user_id;
    public $user2_id;
    public $date;


    public static function getUserFriends(?User $user = null)
    {
        if (!$user) {
            $user = $GLOBALS["user"];
        }

        $allFriends = QueryBuilder::select(Friend::getFields(['user2_id']))
            ->from(Friend::$table)
            ->where("user_id", "=", (int)$user->get("id"))
            ->get();

        $allFriendsList = [];
        if ($allFriends)
            foreach ($allFriends as $friends) {
                $allFriendsList[] = $friends["user2_id"];
            }

        $allFriends = join(",", $allFriendsList);

        if(strlen($allFriends) > 0)
            $allFriends = User::where("id", "IN", "( {$allFriends} )")->get();
        else
            $allFriends = [];

        return $allFriends;
    }

    public static function getUserFriendsInvite(?User $user = null)
    {
        if (!$user) {
            $user = $GLOBALS["user"];
        }

        $invitations = QueryBuilder::select(Invitation::getFields(['user_id']))
            ->from(Invitation::$table)
            ->where("user2_id", "=", (int)$user->get("id"))
            ->get();


        $allFriendsList = [];
        if ($invitations)
            foreach ($invitations as $friends) {
                $allFriendsList[] = $friends["user_id"];
            }

        $invitations = join(",", $allFriendsList);

        if ($invitations):
            $invitations = User::where("id", "IN", "( {$invitations} )")->get();
        else:
            $invitations = [];
        endif;

        return $invitations;
    }

    public static function addFriend(int $id, int $id2)
    {
        Friend::create([
            "user_id" => $id,
            "user2_id" => $id2,
            "date" => (new DateTime())->format('Y-m-d H:i:s')
        ]);

        Friend::create([
            "user_id" => $id2,
            "user2_id" => $id,
            "date" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
}