<?php

namespace Core\System;

use Core\System\Route;
use Core\System\Request;
use Core\System\Response;

class Api{
    private $allRoutes = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "DELETE" => [],
    ];
    private $method;
    private $request;
    private $requestObject;

    public function get(String $route, String $controller){
        $this->allRoutes['GET'][] = new Route($route,$controller);
    }

    public function post(String $route, String $controller){
        $this->allRoutes['POST'][] = new Route($route,$controller);
    }

    public function put(String $route, String $controller){
        $this->allRoutes['PUT'][] = new Route($route,$controller);
    }

    public function delete(String $route, String $controller){
        $this->allRoutes['DELETE'][] = new Route($route,$controller);
    }

    public function getAllRoutes():Array{
        return $this->allRoutes;
    }

    public function group(String $groupName, $asd){
        $fakeApi = new Api();
        $asd($fakeApi);
        $allRoutes = $fakeApi->getAllRoutes();

        foreach ($this->allRoutes as $key => $value) {
            foreach ($allRoutes[$key] as $val) {
                $val->unshiftRoute($groupName);
                $this->allRoutes[$key][] = $val;
            }
        }


        unset($fakeApi);
    }

    public function run(){
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->request = $_SERVER['REQUEST_URI'];
        if(strpos($this->request,"?")){
            $this->request = explode("?",$this->request)[0];
        }
        $this->requestObject = new Request();
        $this->handlingRoute();
    }

    public function handlingRoute(){
        $request = explode("/",$this->request);
        $request = array_values(array_filter($request, 'strlen'));
        $requestCount = count($request);
        $selectedRoute = null;

        foreach ($this->allRoutes[$this->method] as $route) {
            if($route->getCount() === $requestCount){
                if($route->validateRoute($request)){
                    $selectedRoute = $route;
                    break;
                }
            }
        }

        if($selectedRoute == null){
            Logger::getInstance()->warning("Próba wejścia na nieistniejący adres: {$this->request}");
            Response::error("Ścieżka dostępu: {$this->request} nie została zdefiniowana.",404);
        }else{
            $selectedRoute->handleRequest($this->requestObject);
        }
    }
}