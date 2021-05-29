<?php

namespace Core\Controller;

use Core\Middleware\Auth;
use Core\Model\Gift;
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
use Service\GiftService;

class GiftController implements IController{

    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getGift(Request $request)
    {
        Validator::validateRequest($request)
            ->get("giftKey")->length(6,6);

        $giftKey = $request->get("giftKey");

        Response::json(GiftService::validateGiftKey($giftKey));
    }

    public function useGift(Request $request)
    {
        Validator::validateRequest($request)
            ->get("giftKey")->length(6,6);

        GiftService::useGiftKey($request->get("giftKey"));
        Response::success("Kod użyty prawidłowo. Możesz już cieszyć się jego działaniem.");
    }
}