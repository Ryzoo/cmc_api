<?php
namespace Core\System;

class Request{
    private $post;
    private $file;
    private $get;

    public function __construct(){
        $this->post = json_decode(file_get_contents('php://input'),true);
        $this->post = $this->post ?? $_POST;
        $this->get = $this->get ?? $_GET;
        $this->file = $_FILES;
    }

    public function get(String $param, $fromGet = false){
        if($fromGet){
            return $this->get[$param] ?? NULL;
        }
        return $this->post[$param] ?? NULL;
    }

    public function file(String $name){
        foreach ($this->file as $value) {
            if( $value["name"] === $name) return $value;
        }
        return NULL;
    }

}