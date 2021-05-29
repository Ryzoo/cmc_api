<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.09.18
 * Time: 06:58
 */

namespace Core\Model;

use Core\System\BasicModel;

class RolePermission extends BasicModel
{
    public static $table = "RolePermission";
    public static $fields = ["id", "role_id", "permission_id"];
    public static $fieldsType = [
        "int(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`)",
        "int(11) NOT NULL",
        "int(11) NOT NULL",
    ];
    public static $relations = [];

    public $id;
    public $role_id;
    public $permission_id;

    public static function addPermissionById(int $roleId, int $permissionId)
    {
        $rolePerm = RolePermission::where("role_id","=",$roleId)->where("permission_id","=",$permissionId)->get();

        if(count($rolePerm) === 0){
            RolePermission::create([
                "role_id" => $roleId,
                "permission_id" => $permissionId
            ]);
        }
    }

    public static function removePermissionById(int $roleId, int $permissionId)
    {
        $rolePerm = RolePermission::where("role_id","=",$roleId)->where("permission_id","=",$permissionId)->get();

        if(count($rolePerm) === 1){
            $rolePerm[0]->delete();
        }
    }
}