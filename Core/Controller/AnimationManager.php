<?php

namespace Core\Controller;

use Core\Models\SharedAnimation;
use Core\Models\User;
use Core\System\FileManager;
use Core\System\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Models\Animation;
use Core\Middlewares\Auth;
use Core\System\Validator;

class AnimationManager implements IController{

    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function get(Request $request,int $id){
        $animation = Animation::where("id","=",$id)->get(1);
        if(isset($animation[0])){
            $sharedUser = User::find( $animation[0]->user_id );
            $animation[0]->url = FileManager::getUserFileUrl($animation[0]->url.".mp4",$sharedUser,"animation/".$id);
            $animation[0]->main_image = FileManager::getUserFileUrl($animation[0]->main_image.".jpeg",$sharedUser,"animation/".$id);
        }
        return new Response($animation,200);
    }

    public function update(Request $request,int $id){
        $animation = Animation::where("id","=",$id)->get(1);
        if(isset($animation[0])){
            $animation = $animation[0];
            $animation->name = $request->get("name");
            $animation->user_id = $GLOBALS["user"]->id;
            if(!$request->get("onlyData")) {
                FileManager::deleteUserFile($animation->main_image.".jpeg",null,"animation/".$animation->id);
                $animation->main_image = $GLOBALS["generator"]->generateString(25,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
                FileManager::putImgFromData($request->get("first_img"),$animation->main_image.".jpeg","image/jpeg","animation/".$animation->id);
            }
            $animation->path_field = $request->get("pathField");
            $animation->frame_data = json_encode($request->get("frameData"));
            $animation->object_in_animation = json_encode($request->get("objectInAnimation"));
            $animation->description = $request->get("description") ? $request->get("description") : "";
            $animation->equipment = json_encode($request->get("equipment") ? $request->get("equipment") : "");
            $animation->age_category = $request->get("age_category") ? $request->get("age_category") : "";
            $animation->game_field = $request->get("game_field") ? $request->get("game_field") : "";
            $animation->tips = $request->get("tips") ? $request->get("tips") : "";

            $animation->date_add = (new \DateTime())->format("Y-m-d");
            if(!$request->get("onlyData")) {
                FileManager::deleteUserFile($animation->url.".mp4",null,"animation/".$animation->id);
                $animation->url = $GLOBALS["generator"]->generateString(25,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
            }

            $animation->save();

            if(!$request->get("onlyData")){

                $newName = $animation->url;
                $userDir = FileManager::getUserDir(false,null,"animation/".$animation->id);
                $fileName = $animation->name;

                $currentYear = (new \DateTime())->format("Y");

                exec("echo \"#!/bin/bash\" > ./user_script_{$newName}.sh");
                $scriptFile = fopen("user_script_{$newName}.sh", "w");
                fwrite($scriptFile, " mkdir zdj_{$newName}");
                fwrite($scriptFile, " && phantomjs ./runner.js {$animation->id} {$newName} {$GLOBALS["user"]->login_token}");
                fwrite($scriptFile, " && cd ./zdj_{$newName}/ && ffmpeg -framerate 60 -i frame_%00d.jpeg -c:v libx264 -preset slow -crf 22 -pix_fmt yuv420p -c:a libvo_aacenc -b:a 128k -metadata title=\"{$fileName}\" -metadata album_artist=\"Club Management Center\" -metadata author=\"Club Management Center\" -metadata year=\"{$currentYear}\" {$newName}.mp4");
                fwrite($scriptFile, " && mv ./{$newName}.mp4 .{$userDir}/{$newName}.mp4");
                fwrite($scriptFile, " && cd ../ && rm -rf ./zdj_{$newName}");
                fwrite($scriptFile, " && rm -f ./user_script_{$newName}.sh");
                fwrite($scriptFile, ' && curl --data "login_token='.$GLOBALS["user"]->login_token.'&unique='.$GLOBALS["user"]->unique_token.'&user_id='.$GLOBALS["user"]->id.'&title=Serwer zakończył renderowanie filmu.&content=Twój projekt: <b>'.$animation->name.'</b> właśnie został wyrenderowany i jest już dostępny do pobrania lub podglądu." https://api.centrumklubu.pl/notifications/add');

                fclose($scriptFile);

                exec("chmod +x ./user_script_{$newName}.sh");
                exec("chmod u+s ./user_script_{$newName}.sh");
                exec("su CMCAdmin ./user_script_{$newName}.sh > out.txt 2>out2.txt &");
            }
            return new Response(true,200);
        }
        return new Response(null,501);
    }

    public function animationsLast(Request $request){
        $animations = Animation::where("user_id","=",(int)$GLOBALS["user"]->id)->orderBy('date_add', true)->get(6);
        foreach ($animations as $anim){
            $anim->url = FileManager::getUserFileUrl($anim->url.".mp4",null,"animation/".$anim->id);
            $anim->main_image = FileManager::getUserFileUrl($anim->main_image.".jpeg",null,"animation/".$anim->id);
        }
        return new Response($animations,200);
    }

    public function all(Request $request){
        $animations = Animation::where("user_id","=",(int)$GLOBALS["user"]->id)->orderBy('date_add', true)->get();
        foreach ($animations as $anim){
            $anim->url = FileManager::getUserFileUrl($anim->url.".mp4",null,"animation/".$anim->id);
            $anim->main_image = FileManager::getUserFileUrl($anim->main_image.".jpeg",null,"animation/".$anim->id);
            $anim->path_field = '';
            $anim->frame_data = '';
            $anim->object_in_animation = '';
            $anim->game_field = '';
            $anim->age_category = '';
            $anim->tips = "";
        }
        return new Response($animations,200);
    }

    public function allShared(Request $request){
        $sharedList = SharedAnimation::where("shared_user_id", "=",(int)$GLOBALS["user"]->id)->get();
        $sharedString = "";
        foreach ($sharedList as $key => $sh){
            if($key === 0){
                $sharedString .= "'".$sh->animation_id."'";
            }else $sharedString .= ", "."'".$sh->animation_id."'";
        }

        $animations = [];

        if(strlen($sharedString) > 0){
            $animations = Animation::where("id","IN","(".$sharedString.")")->orderBy('date_add', true)->get();
            foreach ($animations as &$anim){
                $sharedUser = User::find( $anim->user_id );
                $anim->url = FileManager::getUserFileUrl($anim->url.".mp4",$sharedUser,"animation/".$anim->id);
                $anim->main_image = FileManager::getUserFileUrl($anim->main_image.".jpeg",$sharedUser,"animation/".$anim->id);
                $anim->path_field = '';
                $anim->frame_data = '';
                $anim->object_in_animation = '';
                $anim->game_field = '';
                $anim->age_category = '';
                $anim->tips = "";
            }
        }

        return new Response($animations,200);
    }

    public function delete(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNotNull();

        $animations = Animation::find($request->get("id"));
        $animations->delete();

        FileManager::deleteUserDir("animation/".$request->get("id"));

        return new Response(true,200);
    }

    public function checkRender(Request $request){
        Validator::validateRequest($request)
            ->get("url")->isNotNull()
            ->get("name")->length(1);

        $url = str_replace("//".$_SERVER['SERVER_NAME']."/", './', $request->get("url"));

        Response::file($url,$request->get("name"),200);
    }

    public function checkRenderImg(Request $request){
        Validator::validateRequest($request)
            ->get("url")->isNotNull()
            ->get("name")->length(1);

        $url = str_replace("//".$_SERVER['SERVER_NAME']."/", './', $request->get("url"));

        Response::file($url,$request->get("name"),200);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("name")->length(1,255)
            ->get("frameData")->isNotNull()
            ->get("objectInAnimation")->isNotNull()
            ->get("pathField")->length(5);

        $animation = new Animation();
        $animation->name = $request->get("name");
        $animation->user_id = $GLOBALS["user"]->id;
        $animation->main_image = $GLOBALS["generator"]->generateString(25,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $animation->path_field = $request->get("pathField");
        $animation->frame_data = json_encode($request->get("frameData"));
        $animation->object_in_animation = json_encode($request->get("objectInAnimation"));
        $animation->description = $request->get("description") ? $request->get("description") : "";

        $animation->game_field = $request->get("game_field") ? $request->get("game_field") : "";
        $animation->equipment = json_encode($request->get("equipment") ? $request->get("equipment") : "");
        $animation->age_category = $request->get("age_category") ? $request->get("age_category") : "";
        $animation->tips = $request->get("tips") ? $request->get("tips") : "";

        $animation->date_add = (new \DateTime())->format("Y-m-d");
        $animation->url = $GLOBALS["generator"]->generateString(25,"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $animation->save();

        $newName = $animation->url;
        $userDir = FileManager::getUserDir(false,null,"animation/".$animation->id);
        $fileName = $animation->name;

        $currentYear = (new \DateTime())->format("Y");

        FileManager::putImgFromData($request->get("first_img"),$animation->main_image.".jpeg","image/jpeg","animation/".$animation->id);

        exec("echo \"#!/bin/bash\" > ./user_script_{$newName}.sh");
        $scriptFile = fopen("user_script_{$newName}.sh", "w");
        fwrite($scriptFile, " mkdir zdj_{$newName}");
        fwrite($scriptFile, " && phantomjs ./runner.js {$animation->id} {$newName} {$GLOBALS["user"]->login_token}");
        fwrite($scriptFile, " && cd ./zdj_{$newName}/ && ffmpeg -framerate 60 -i frame_%00d.jpeg -c:v libx264 -preset slow -crf 22 -pix_fmt yuv420p -c:a libvo_aacenc -b:a 128k -metadata title=\"{$fileName}\" -metadata album_artist=\"Club Management Center\" -metadata author=\"Club Management Center\" -metadata year=\"{$currentYear}\" {$newName}.mp4");
        fwrite($scriptFile, " && mv ./{$newName}.mp4 .{$userDir}/{$newName}.mp4");
        fwrite($scriptFile, " && cd ../ && rm -rf ./zdj_{$newName}");
        fwrite($scriptFile, " && rm -f ./user_script_{$newName}.sh");
        fwrite($scriptFile, ' && curl --data "login_token='.$GLOBALS["user"]->login_token.'&unique='.$GLOBALS["user"]->unique_token.'&user_id='.$GLOBALS["user"]->id.'&title=Serwer zakończył renderowanie filmu.&content=Twój projekt: <b>'.$animation->name.'</b> właśnie został wyrenderowany i jest już dostępny do pobrania lub podglądu." https://api.centrumklubu.pl/notifications/add');

        fclose($scriptFile);

        exec("chmod +x ./user_script_{$newName}.sh");
        exec("chmod u+s ./user_script_{$newName}.sh");
        exec("su CMCAdmin ./user_script_{$newName}.sh > out.txt 2>out2.txt &");

        return new Response($newName,200);
    }
}