<?php

namespace Core\Controller;

use Core\Model\ExtendedProfile;
use Core\Model\License;
use Core\Model\Notification;
use Core\Model\User;
use Core\System\Config;
use Core\System\EmailSender;
use Core\System\File;
use Core\System\FileManager;
use Core\System\Contract\IController;
use Core\System\Logger;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;

class LogsController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function getAll(Request $request)
    {
        $limit = $request->get("limit",true);

        $returnLogs = [];

        $handle = fopen(__DIR__."/../../".Config::config("logPath"), "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $m = null;

                preg_match('/\[([^\]]+)\]/', $line, $m );
                $date = $m[1];
                $line = preg_replace('/\[([^\]]+)\]/', "", $line);

                preg_match('/logger.([A-z]+):/', $line, $m );
                $type = $m[1];
                $line = preg_replace('/logger.([A-z]+):/', "", $line);

                $content = $line;

                $returnLogs[] = [
                    "date" => $date,
                    "type" => $type,
                    "content" => $content,
                ];
            }
            fclose($handle);
        } else {
            Logger::getInstance()->error("Nie można otowrzyć pliku z logami.");
        }

        $returnLogs = array_reverse($returnLogs);

        if(count($returnLogs) >= $limit){
            $returnLogs = array_splice($returnLogs,0,$limit);
        }

        Response::json($returnLogs);
    }

    public function removeAll(Request $request)
    {
        file_put_contents(__DIR__."/../../".Config::config("logPath"), "");
        Response::json(true);
    }
}