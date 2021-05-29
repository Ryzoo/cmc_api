<?php

namespace Core\System;

use Core\Model\User;
use Exception;

class FileManager
{
    public static function getFileDefaultUrl(string $fileDefault, bool $asData = false)
    {
        $path = $fileDefault;

        switch ($fileDefault) {
            case "default":
                $path =  "/resources/profile_default.svg";
        }

        if($path !== $fileDefault){
            if($asData){
                return FileManager::getB64Data($path);
            }else{
                $path = "//{$_SERVER['SERVER_NAME']}" . $path;
            }
        }

        return $path;
    }

    static public function getB64Data(string $path):string{
        $type = FileManager::getFileTypeByExt(pathinfo($path, PATHINFO_EXTENSION));
        $data = base64_encode(file_get_contents($path));
        $base64 = "data:{$type};base64,{$data}";
        return $base64;
    }

    static public function getUserDir($withServer = true, ?User $user = null, $catalog = null)
    {
        if (is_null($user)) {
            $user = $GLOBALS["user"];
        }

        $uniqueToken = $user->get("unique_token");

        if (isset($user) && isset($uniqueToken)) {
            $dir = '';
            $firstName = trim($user->firstname);
            $lastName = trim($user->lastname);

            if (!$withServer) {
                $dir = "./resources/" . $firstName . "_" . $lastName . "_" . $uniqueToken;
            } else {
                $dir = "//" . $_SERVER['SERVER_NAME'] . "/resources/" . $firstName . "_" . $lastName . "_" . $uniqueToken;
            }

            if (isset($catalog)) {
                $dir .= "/" . $catalog;
            }

            if (!file_exists($dir) && !$withServer) {
                mkdir($dir, 0777, true);
                echo($dir);
            }

            return $dir;
        } else return null;
    }

    static public function deleteUserDir($catalog, ?User $user = null)
    {
        $dir = FileManager::getUserDir(false, $user, $catalog);
        if (file_exists($dir)) {
            exec("rm -rf ../." . $dir);
        }
        return true;
    }

    public static function validateSize(string $imgData, ?int $maxMbSize = null)
    {
        if($maxMbSize){
            try{
                $size_in_bytes = (int) (strlen(rtrim($imgData, '=')) * 3 / 4);
                $size_in_kb    = $size_in_bytes / 1024;
                $size_in_mb    = $size_in_kb / 1024;

                if($size_in_mb > ($maxMbSize + 0.5)){
                    Response::error("Maksymalny rozmiar pliku to: {$maxMbSize}mb", 400);
                }
            }
            catch(Exception $e){
                Response::error("Problem z rozmiarem pliku.", 400);
            }
        }
    }

    public static function putFile(string $imgData, array $validTypes, string $catalog = "/", ?User $user = null)
    {
        $ext = self::validateFile($imgData, $validTypes);

        $userDir = FileManager::getUserDir(false);
        $fileName = Generator::generateString(25);

        $img64Data = substr($imgData, strpos($imgData, ",") + 1);
        $img64Data = str_replace(' ', '+', $img64Data);

        try {
            $data64 = base64_decode($img64Data);
            // userDir/fileName.fileExtension
            $fullFileName = $userDir . "/" . $fileName . "." . $ext;
            file_put_contents($fullFileName, $data64);
        } catch (\Exception $e) {

        }

        return File::create($fileName, $ext, $data64, $catalog, $user);
    }

    private static function validateFile(string $b64Data, array $validTypes): string
    {
        $fileType = self::getB64FileType($b64Data);

        if (!in_array($fileType, $validTypes)) {
            Logger::getInstance()->warning("Próba przesłania niedozwolonego pliku: {$fileType}");
            Response::error("Przesłałeś niedozwolony typ pliku.", 400);
        }

        return self::getFileExtensionFromFIleType($fileType);
    }

    private static function getFileExtensionFromFIleType(string $fileType): string
    {
        switch ($fileType) {
            case "image/bmp":
                return "bmp";
            case "image/gif":
                return "gif";
            case "image/x-icon":
                return "ico";
            case "image/jpeg":
                return "jpeg";
            case "image/png":
                return "png";
            case "video/mpeg":
                return "mpeg";
            case "audio/ogg":
                return "oga";
            case "video/ogg":
                return "ogv";
            case "video/mp4":
                return "mp4";
            case "application/pdf":
                return "pdf";
            case "image/svg+xml":
                return "svg";
            case "application/x-7z-compressed":
                return "7z";
            case "application/zip":
                return "zip";
            default:
                Response::error("Użyłeś rozszerzenia pliku którego nie obsługujemy.", 400);
        }
    }

    public static function getFileTypeByExt(string $ext): string
    {
        switch ($ext) {
            case "bmp":
                return "image/bmp";
            case "gif":
                return "image/gif";
            case "ico":
                return "image/x-icon";
            case "jpeg":
                return "image/jpeg";
            case "png":
                return "image/png";
            case "mpeg":
                return "video/mpeg";
            case "oga":
                return "audio/ogg";
            case "ogv":
                return "video/ogg";
            case "mp4":
                return "video/mp4";
            case "pdf":
                return "application/pdf";
            case "svg":
                return "image/svg+xml";
            case "7z":
                return "application/x-7z-compressed";
            case "zip":
                return "application/zip";
            default:
                Response::error("Plik posiada złe rozszerzenie.", 400);
        }
    }

    private static function getB64FileType($b64Data)
    {
        if (substr($b64Data, 0, 5) !== 'data:') {
            Response::error("Błędne dane pliku.", 400);
        }
        return substr($b64Data, 5, strpos($b64Data, ";") - 5);
    }

    public static function loadFileFromField(&$data, array $fields, bool $asData = false)
    {
        if ($data) {
            if (is_object($data)) {
                foreach ($fields as &$field) {
                    if (property_exists(get_class($data), $field) && is_numeric($data->$field)) {
                        $data->$field = self::getUrlByImgFile((int)$data->$field,$asData);
                    } else if (property_exists(get_class($data), $field) && is_string($data->$field)) {
                        $data->$field = self::getFileDefaultUrl($data->$field,$asData);
                    }
                }
            } else if (is_array($data)) {
                foreach ($data as $key => &$item) {
                    $type = is_array($item) ? "array" : false;
                    if (!$type) $type = is_object($item) ? "object" : false;

                    foreach ($fields as &$field) {
                        switch ($type) {
                            case "array":
                                if (isset($item[$field]) && is_numeric($item[$field])) {
                                    $item[$field] = self::getUrlByImgFile((int)$item[$field],$asData);
                                } else if (isset($item[$field]) && is_string($item[$field])) {
                                    $item[$field] = self::getFileDefaultUrl($item[$field],$asData);
                                }
                                break;
                            case "object":
                                if (property_exists(get_class($item), $field) && is_numeric($item->$field)) {
                                    $item->$field = self::getUrlByImgFile((int)$item->$field,$asData);
                                } else if (property_exists(get_class($item), $field) && is_string($item->$field)) {
                                    $item->$field = self::getFileDefaultUrl($item->$field,$asData);
                                }
                                break;
                            default:
                                if ($key === $field && is_numeric($item)) {
                                    $item = self::getUrlByImgFile((int)$item,$asData);
                                } else if ($key === $field && is_string($item)) {
                                    $item = self::getFileDefaultUrl($item,$asData);
                                }
                                break;
                        }
                    }
                }
            }
        }
    }

    private static function getUrlByImgFile(int $imgId, bool $asData = false): string
    {
        if($asData){
            return FileManager::getB64Data(File::getById($imgId)->getPath());
        }
        return File::getById($imgId)->getUrl();
    }
}