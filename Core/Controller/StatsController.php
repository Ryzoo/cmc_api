<?php

namespace Core\Controller;

use Core\Model\ExtendedProfile;
use Core\Model\License;
use Core\Model\Notification;
use Core\Model\User;
use Core\System\Database;
use Core\System\EmailSender;
use Core\System\File;
use Core\System\FileManager;
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;

class StatsController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getDatabaseStat(Request $request){
        Response::json( Database::getStats($request) );
    }

}