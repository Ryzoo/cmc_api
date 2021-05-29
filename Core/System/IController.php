<?php

namespace Core\System;

use Core\System\Request;

interface IController{
    public function middleware(Request $request):bool;
}