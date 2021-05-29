<?php

namespace Core\System\Contract;

use Core\System\Request;

interface IMiddleware{
    public static function check(Request $request):bool;
}