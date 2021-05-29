<?php
namespace Core\System;

use Core\System\Request;
use Core\System\Response;

class Route{
    private $route;
    private $controller;
    private $function;
    private $routeCount;
    private $param = [];

    public function __construct(String $route, String $controller){
        $parseControllerFunction = explode("@",$controller,2);
        $this->route = explode("/",$route);
        $this->route = array_values(array_filter($this->route, 'strlen'));
        $this->routeCount = count($this->route);
        $this->controller = "Core\\Controller\\".$parseControllerFunction[0];
        $this->function = $parseControllerFunction[1];
    }

    public function getCount(): int{
        return $this->routeCount;
    }

    public function validateRoute(Array $request): bool{
        $status = true;
        $param = [];

        foreach($this->route as $key => $routePath){
            $requestPath = $request[$key];
            if($routePath !== $requestPath){
                if( preg_match('/{[A-z]+}/', $routePath) ){
                    $param[] = is_numeric($requestPath) ? (int)$requestPath : $requestPath;
                }else{
                    $status = false;
                    break;
                }
            }
        }

        if($status) $this->param = $param;
        return $status;
    }

    public function handleRequest(Request $request){
        $controller = new $this->controller;
        $fname = $this->function;
        array_unshift($this->param,$request);

        $interfaces = class_implements( $this->controller );

        if ( isset( $interfaces['Core\System\Contract\IController'] ) ){
            $middlewareStatus = $controller->middleware($request);
            if(!$middlewareStatus){
                Logger::getInstance()->warning("Nieudana autoryzacja w kontrolerze: {$this->controller}");
                Response::error("Middleware dla klasy: {$this->controller} zablokowało dostęp.",401);
            }
        }


        call_user_func_array(array($controller,$fname), $this->param);
    }

    public function unshiftRoute(String $path){
        $route = explode("/",$path);
        $route = array_values(array_filter($route, 'strlen'));
        $this->route = array_values(array_merge($route,$this->route));
        $this->routeCount = count($this->route);
    }
}