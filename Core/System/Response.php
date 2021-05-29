<?php
namespace Core\System;

use Core\Model\Permission;

class Response{

    private function __clone(){}
    private function __construct(){}

    public static function json($content, int $code = 200,$exit = true)
    {
        ob_end_clean();
        http_response_code($code);
        echo json_encode($content,JSON_UNESCAPED_UNICODE);
        if($exit) exit();
    }

    public static function error($errorMessage, $code = 404)
    {
        Response::json([
            "error" => $errorMessage
        ],$code);
    }

    public static function success($errorMessage, $code = 200)
    {
        Response::json([
            "success" => $errorMessage
        ],$code);
    }

    public static function fileFromPath($path, $name, $statuCode = 200)
    {
        if(filesize($path) <= 100){
            Response::error("Plik niedostępny.");
        }

        ob_end_clean();
        http_response_code($statuCode);
        header("Cache-Control: public");
        header("Content-Type: {$path}");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:".filesize($file->getPath()));
        header("Content-Disposition: attachment; filename={$name}");
        readfile($path);
        exit();
    }

    public static function file($fileID){
        $file = File::getById($fileID);

        if(filesize($file->getPath()) <= 100){
            Response::error("Plik niedostępny.");
        }

        Permission::check([
            ["full_license_access",null],
            ["simple-owner",$file->getUser()->get("id")]
        ]);

        ob_end_clean();
        http_response_code(200);
        header("Cache-Control: public");
        header("Content-Type: {$file->getType()}");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:".filesize($file->getPath()));
        header("Content-Disposition: attachment; filename={$file->getName()}");
        readfile($file->getPath());
        exit();
    }
}