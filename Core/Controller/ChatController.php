<?php

namespace Core\Controller;

use Core\Model\Friend;
use Core\Model\Message;
use Core\Model\User;
use Core\System\FileManager;
use Core\System\Contract\IController;
use Core\System\QueryBuilder;
use Core\System\Request;
use Core\Middleware\Auth;
use Core\System\Response;
use Core\System\Validator;

class ChatController implements IController
{
    public function middleware(Request $request): bool
    {
        return Auth::check($request);
    }

    public function get(Request $request)
    {
        $asElement = [];
        $asElement[] = "( SELECT COUNT(id) FROM Message WHERE user_from = Friend.user2_id AND user_to = " . ((int)$GLOBALS["user"]->get('id')) . " AND isRead = false) as noReadCount";

        $allCounter = QueryBuilder::select(array_merge(Friend::getFields(['user2_id']), $asElement, User::getFields(['firstname', 'lastname', 'id', 'profile_img', 'unique_token'])))
            ->from(Friend::$table)
            ->joinOn(User::$table, Friend::$table . ".user2_id", User::$table . ".id")
            ->where("Friend.user_id", "=", (int)$GLOBALS["user"]->id)
            ->orderBy(["noReadCount"])
            ->get();

        FileManager::loadFileFromField($allCounter, ["profile_img"]);

        Response::json($allCounter);
    }

    public function setMessageRead(Request $request, int $id)
    {

        $message = Message::find($id);

        if ($message) {
            $message->update([
                "isRead" => 1
            ]);
        } else {
            Response::error("Nie znaleziono obiektu do edycji.", 404);
        }

        Response::json(true, 200);
    }

    public function getMessageList(Request $request, int $userChatId)
    {
        QueryBuilder::update([
            array("name" => "isRead", "value" => 1)
        ])
            ->in(Message::$table)
            ->where("user_from", "=", $userChatId . " AND user_to = " . (int)$GLOBALS["user"]->get("id"))
            ->exec();

        $userId = (int)$GLOBALS["user"]->get("id");
        $chatMessages = Message::where("user_from", "IN", "( {$userChatId}, {$userId} )")
            ->where("user_to", "IN", "( {$userChatId}, {$userId} )")
            ->orderBy(["date"], false)
            ->get($request);

        Response::json($chatMessages);
    }
}