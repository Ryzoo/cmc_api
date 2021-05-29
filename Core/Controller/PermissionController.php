<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.09.18
 * Time: 07:17
 */

namespace Core\Controller;


use Core\Middleware\Auth;
use Core\Model\Permission;
use Core\Model\RolePermission;
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;
use Core\System\Validator;

class PermissionController implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function get(Request $request)
    {
        $roles = Permission::all();
        Response::json($roles);
    }

    public function getOne(Request $request, int $id)
    {
        $permission = Permission::find($id);
        $rolePerm = RolePermission::where('permission_id','=',$id)->get();

        Response::json([
            "permission" => $permission,
            "rolePermission" => $rolePerm
        ]);
    }

    public function delete(Request $request, int $id )
    {
        $permList = RolePermission::where('permission_id','=',$id)->get();

        foreach($permList as $perm){
            $perm->delete();
        }

        Permission::remove($id);

        Response::json(true);
    }

    public function save(Request $request, int $id)
    {
        Validator::validateRequest($request)
            ->get("name")->length(3,50);


        $name = $request->get("name");
        $name = trim($name);
        $name = strtolower($name);
        $name = str_replace(" ","_",$name);

        $permission = Permission::find($id);
        $permission->update([
            "name" => $name
        ]);

        $permissionArray = $request->get("permission");

        foreach ($permissionArray as $perm){
            if($perm["permission"]) RolePermission::addPermissionById($perm["id"],$permission->id);
            else RolePermission::removePermissionById($perm["id"],$permission->id);
        }

        Response::json(true);
    }

    public function add(Request $request)
    {
        Validator::validateRequest($request)
            ->get("name")->length(3,50)
            ->get("permission")->isNotNull();

        $name = $request->get("name");
        $name = trim($name);
        $name = strtolower($name);
        $name = str_replace(" ","_",$name);

        $permission = Permission::create([
            "name" => $name
        ]);

        $permissionArray = $request->get("permission");

        foreach ($permissionArray as $perm){
            if($perm["permission"]) RolePermission::addPermissionById($perm["id"],$permission->id);
        }

        Response::json($permission);
    }
}