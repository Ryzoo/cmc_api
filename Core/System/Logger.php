<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 26.09.18
 * Time: 06:25
 */

namespace Core\System;

use Monolog\Handler\StreamHandler;

class Logger
{
    private static $instance;

    public static function getInstance():\Monolog\Logger
    {
        if(!self::$instance){
            $conf = __DIR__ . "/../../" . Config::config("logPath");
            $isDebug = Config::config("environment") === 'dev' ? true : false;
            self::$instance = new \Monolog\Logger('logger');
            try {
                $stream = new StreamHandler($conf, $isDebug ? \Monolog\Logger::DEBUG : \Monolog\Logger::INFO);
                self::$instance->pushHandler($stream);
            } catch (\Exception $e) {
                Response::error("Nie można uruchomić logera",500);
            }
        }

        return self::$instance;
    }
}