<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.09.18
 * Time: 06:58
 */

namespace Core\Model;

use Core\System\BasicModel;
use Core\System\Logger;
use Core\System\Response;

class Permission extends BasicModel
{
    public static $table = "Permission";
    public static $fields = ["id", "name"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "varchar(50) NOT NULL",
    ];
    public static $relations = [];


    public static function userHaveByName(string $permName, ?User $user = null)
    {
        if (!$user) {
            $user = $GLOBALS['user'];
        }

        $permName = trim($permName);
        $permName = str_replace(" ", "_", $permName);
        $permName = strtolower($permName);

        $perm = Permission::where("name", "LIKE", $permName)->get();
        if (!isset($perm[0])) {
            Logger::getInstance()->warning("Próba dostępu do nieistniejącego uprawnienia: {$permName}");
            return false;
        }

        $rolePerm = RolePermission::where("role_id", "=", $user->get('role_id'))
            ->where("permission_id", "=", $perm[0]->get("id"))
            ->get();

        if (!isset($rolePerm[0])) {
            return false;
        }

        return true;
    }

    public static function check(array $permission, ?User $user = null, $allCheck = false)
    {
        if (!$user) {
            $user = $GLOBALS['user'];
        }

        foreach ($permission as $perm) {
            $key = $perm[0];
            $value = $perm[1];
            $isChecked = false;

            switch ($key) {
                case "simple-id":
                    $isChecked = $value == $user->get("id");
                    break;
                case "simple-owner":
                    $isChecked = $value == $user->get("id");
                    break;
                default:
                    $isChecked = Permission::userHaveByName($key, $user);
                    break;
            }

            if ($allCheck && !$isChecked) {
                Logger::getInstance()->warning("Użytkownik {$GLOBALS['id']} próbował wykonać akcję bez uprawnień.");
                Response::error('Próbowałeś wykonać akcję nie posiadając uprawnien. Ten fakt został zgłoszony.');
                return false;
            } else if (!$allCheck && $isChecked) {
                return true;
            }
        }

        if (!$allCheck) {
            Logger::getInstance()->warning("Użytkownik {$GLOBALS['id']} próbował wykonać akcję bez uprawnień.");
            Response::error('Próbowałeś wykonać akcję nie posiadając uprawnien. Ten fakt został zgłoszony.');
            return false;
        }

        return true;
    }

    public $id;
    public $name;
}