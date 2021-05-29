<?php

namespace Core\System\Contract;

use Core\System\Request;

interface IController{
    public function middleware(Request $request):bool;
}