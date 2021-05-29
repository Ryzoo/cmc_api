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
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;
use Core\System\Validator;

class RoleController implements IController
{
    public function middleware(Request $request): bool
    {
        return Auth::check($request);
    }

    public function get(Request $request)
    {
        $roles = Role::all();
        Response::json($roles);
    }

    public function getOne(Request $request, int $id)
    {
        $role = Role::find($id);
        if($role->get('form_id'))
            $role->form_field = FormField::where("form_id","=",$role->get('form_id'))->get();

        Response::json($role);
    }

    public function delete(Request $request, int $id)
    {
        $permList = RolePermission::where('role_id', '=', $id)->get();

        foreach ($permList as $perm) {
            $perm->delete();
        }

        $role = Role::find($id);

        if($role->get('form_id')){
            Form::remove($role->get('form_id'));
            $ff = FormField::where('form_id','=',$role->get('form_id'));
            $fa = FormAnswer::where('form_id','=',$role->get('form_id'));

            foreach ($ff as $perm) {
                $perm->delete();
            }

            foreach ($fa as $perm) {
                $perm->delete();
            }
        }

        $role->delete();
        Response::json(true);
    }

    public function save(Request $request, int $id)
    {
        Validator::validateRequest($request)
            ->get("name")->length(3, 50)
            ->get("description")->length(0, 500)
            ->get("powers")->length(0, 500)
            ->get("fields")->isNotNull();

        $role = Role::find($id);

        $formId = null;
        $fields = $request->get("fields");

        if(is_array($fields) && count($fields) >= 1 && !$role->get('form_id')){
            $form = Form::create([
                "name" => "role_form_{$request->get("name")}"
            ]);
            $formId = $form->get('id');
            FormField::createFromFieldList($formId,$fields);

            $role->update([
                "name" => $request->get("name"),
                "description" => $request->get("description"),
                "powers" => $request->get("powers"),
                "form_id" => $formId
            ]);
        }else{

            $role->update([
                "name" => $request->get("name"),
                "description" => $request->get("description"),
                "powers" => $request->get("powers"),
            ]);

            $roleFormFields = FormField::where("form_id","=",$role->get('form_id'))->get();

            foreach($roleFormFields as $field){
                $index = array_search($field->id, array_column($fields, 'id'));
                if($index !== false){
                    array_splice($fields,$index,1);
                }else{
                    FormField::remove($field->id);
                }
            }

            if(count($fields) >= 1)
                FormField::createFromFieldList($role->get('form_id'),$fields);
        }

        Response::json(true);
    }

    public function add(Request $request)
    {
        Validator::validateRequest($request)
            ->get("name")->length(3, 50)
            ->get("description")->length(0, 500)
            ->get("powers")->length(0, 500)
            ->get("fields")->isNotNull();

        $fields = $request->get('fields');

        $formId = null;

        if(is_array($fields) && count($fields) >= 1){
            $form = Form::create([
                "name" => "role_form_{$request->get("name")}"
            ]);
            $formId = $form->get('id');
            FormField::createFromFieldList($formId,$fields);
        }

        $role = Role::create([
            "name" => $request->get("name"),
            "description" => $request->get("description"),
            "powers" => $request->get("powers"),
            'form_id' => $formId
        ]);

        Response::json(true);
    }
}