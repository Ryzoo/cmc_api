<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 25.09.18
 * Time: 16:47
 */

namespace Core\System;


use Core\Model\User;
use Core\System\Contract\IFile;

class File implements IFile
{
    private $id;
    private $name;
    private $extension;
    private $fullName;
    private $fileModel;
    private $url;
    private $path;
    private $catalog;
    private $user;

    public static function getById(int $id): File
    {
        return new File($id);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public static function create($name, $extension, $data, $usrCatalog = "/", $user = null){
        if (!$user) {
            $user = $GLOBALS["user"];
        }

        $fullName = $name . "." . $extension;
        $path = FileManager::getUserDir(false, $user, $usrCatalog) . "/" .$fullName;

        file_put_contents($path,$data);

        $fileModel = \Core\Model\File::create([
            "name" => $name,
            "extension" => $extension,
            "catalog" => $usrCatalog,
            "user_id" => $user->get("id"),
        ]);

        return self::getById($fileModel->get("id"));
    }

    public function getType():string {
        return FileManager::getFileTypeByExt($this->extension);
    }

    public function __construct($id)
    {
        $this->fileModel = \Core\Model\File::find($id);

        if (!$this->fileModel) {
            Response::error("Nie znaleziono podanego pliku", 404);
        }

        $name = $this->fileModel->get("name");
        $extension = $this->fileModel->get("extension");
        $catalog = $this->fileModel->get("catalog");
        $userID = $this->fileModel->get("user_id");

        $user = User::find($userID);

        $this->id = $id;
        $this->name = $name;
        $this->extension = $extension;
        $this->user = $user;
        $this->catalog = $catalog;
        $this->fullName = $name . "." . $extension;

        $this->path = FileManager::getUserDir(false, $this->user, $catalog);
        $this->url = FileManager::getUserDir(true, $this->user, $catalog);

    }

    public function delete()
    {
        $this->fileModel->delete(false);

        if (file_exists($this->getPath())) {
            unlink($this->getPath());
        }
    }

    public function move(string $newCatalog)
    {
        $pathOld = $this->getPath();
        $pathNew = FileManager::getUserDir(false, $this->user, $newCatalog) . "/" . $this->fullName;
        if (file_exists($pathOld)) {
            rename($pathOld, $pathNew);
            $this->fileModel->update([
                "catalog" => $newCatalog
            ]);
        }
    }

    public function copy()
    {
        $fileName = Generator::generateString(25);
        return File::create( $fileName, $this->extension, $this->getData(),$this->catalog,$this->user);
    }

    public function getData()
    {
        return file_get_contents($this->getPath());
    }

    public function getId()
    {
        return (int)$this->id;
    }

    public function getName($withExt = true)
    {
        return $withExt ? $this->fullName : $this->name;
    }

    public function getUrl($toCatalogOnly = false)
    {
        return $toCatalogOnly ? $this->url : $this->url . "/" . $this->fullName;
    }

    public function getPath($toCatalogOnly = false)
    {
        return $toCatalogOnly ? $this->path : $this->path . "/" . $this->fullName;
    }
}