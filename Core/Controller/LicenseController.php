<?php

namespace Core\Controller;

use Core\Model\License;
use Core\Model\User;
use Core\Model\UserLicense;
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;

class LicenseController implements IController
{
    public function middleware(Request $request): bool
    {
        return Auth::check($request);
    }

    public function get(Request $request)
    {
        $licenses = License::all($request);
        Response::json($licenses);
    }

    public function getOne(Request $request, int $id)
    {
        $license = License::find($id);
        Response::json($license);
    }

    public function delete(Request $request, int $id)
    {
        License::remove($id);

        $lic = UserLicense::where("user_id","=",$id)->get();

        foreach($lic as $l){
            $l->delete();
        }

        Response::json(true);
    }

    public function save(Request $request, int $id)
    {
        Validator::validateRequest($request)
            ->get("name")->length(3, 255)
            ->get("possiblePlace")->length(3, 500)
            ->get("possibleRole")->length(1, 500)
            ->get("powers")->length(3, 500)
            ->get("siteUrl")->length(3, 500)
            ->get("price")->length(3, 255);

        $license = License::find($id);

        $license->update([
            "name" => $request->get("name"),
            "possiblePlace" => $request->get("possiblePlace"),
            "description" => $request->get("description"),
            "siteUrl" => $request->get("siteUrl"),
            "possibleRole" => $request->get("possibleRole"),
            "powers" => $request->get("powers"),
            "price" => $request->get("price")
        ]);

        Response::json(true);
    }

    public function add(Request $request)
    {
        Validator::validateRequest($request)
            ->get("name")->length(3, 255)
            ->get("description")->length(3, 500)
            ->get("possiblePlace")->length(3, 500)
            ->get("possibleRole")->length(1, 500)
            ->get("powers")->length(3, 500)
            ->get("siteUrl")->length(3, 500)
            ->get("price")->length(3, 255);

        License::create([
            "name" => $request->get("name"),
            "description" => $request->get("description"),
            "siteUrl" => $request->get("siteUrl"),
            "possiblePlace" => $request->get("possiblePlace"),
            "possibleRole" => $request->get("possibleRole"),
            "powers" => $request->get("powers"),
            "price" => $request->get("price")
        ]);

        Response::json(true);
    }

    public function getLicenseByRole(Request $request, int $role_id)
    {
        $licenses = License::where("possibleRole","LIKE","%{$role_id}%")->get();
        Response::json($licenses);
    }
}