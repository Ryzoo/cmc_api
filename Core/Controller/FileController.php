<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 27.09.18
 * Time: 07:50
 */

namespace Core\Controller;

use Core\Middleware\Auth;
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;

class FileController  implements IController
{
    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function download(Request $request, int $id)
    {
        Response::file($id);
    }
}