<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 29.09.18
 * Time: 07:17
 */

namespace Core\Controller;

use Core\Middleware\Auth;
use Core\Model\Form;
use Core\Model\FormAnswer;
use Core\Model\FormField;
use Core\Model\Role;
use Core\Model\RolePermission;
use Core\Model\User;
use Core\Model\UserLicense;
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;
use Core\System\Validator;

class UserLicenseController implements IController
{
    public function middleware(Request $request): bool
    {
        return Auth::check($request);
    }

    public function get(Request $request)
    {
        $lic = UserLicense::all($request);
        Response::json($lic);
    }

    public function getOne(Request $request, int $id)
    {
        $lic = UserLicense::find($id);
        Response::json($lic);
    }

    public function delete(Request $request, int $id)
    {
        UserLicense::remove($id);
        Response::json(true);
    }

    public function save(Request $request, int $id)
    {
        Validator::validateRequest($request)
            ->get("user_id")->isNumber()
            ->get("license_id")->isNumber()
            ->get("date_end")->isNotNull();

        $lic = UserLicense::find($id);

        $lic->update([
            "user_id" => $request->get("user_id"),
            "license_id" => $request->get("license_id"),
            "date_end" => $request->get("date_end")
        ]);

        Response::json(true);
    }

    public function add(Request $request)
    {
        Validator::validateRequest($request)
            ->get("user_id")->isNumber()
            ->get("license_id")->isNumber()
            ->get("date_end")->isNotNull();

        UserLicense::create([
            "user_id" => $request->get("user_id"),
            "license_id" => $request->get("license_id"),
            "date_end" => $request->get("date_end")
        ]);

        Response::json(true);
    }
}