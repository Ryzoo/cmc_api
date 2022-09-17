<?php
/**
 * Created by PhpStorm.
 * User: ryzoo
 * Date: 05.01.19
 * Time: 16:35
 */

namespace Core\Job;

use Core\Model\Animation;
use Core\Model\RenderQueue;
use Core\Model\User;
use Core\System\Contract\BaseJob;

class RenderAnimationJob extends BaseJob
{
    public function __construct(string $name, string $date)
    {
        parent::__construct($name, $date);
    }

    public function closure()
    {
        $actualRender = RenderQueue::where("is_render", "=", "TRUE")->get();

        if (count($actualRender) < 1) {
            $newRender = RenderQueue::where("is_render", "=", "FALSE")->where("is_end", "=", "FALSE")->get();


            if ($newRender && count($newRender) >= 1) {

                $newRenderElement = null;

                for ($i = 0; $i < count($newRender); $i++) {
                    $newRenderElement = $newRender[$i];
                    $actualIdRendered = RenderQueue::where("is_render", "=", "TRUE")
                        ->where("animation_id", "=", $newRenderElement->get("animation_id"))->get();

                    if ($actualIdRendered && count($actualIdRendered) > 0) {
                        $newRenderElement = null;
                    } else {
                        break;
                    }

                }

                if ($newRenderElement) {
                    $newRenderElement->update([
                        "is_render" => 1
                    ]);
                    $this->renderAnimation($newRenderElement);
                }
            }
        }
    }

    public function renderAnimation($renderElement)
    {
        echo "Start render anim";
        $animationId = $renderElement->get("animation_id");
        $dir = "/home/forge/api.centrumklubu.pl/";
        $anim = Animation::find($animationId);

        echo "Anim finded";

        if (!$anim) {
            $renderElement->update([
                "is_end" => 1,
                "is_render" => 0
            ]);
        } else {

            echo "Start";

            $newName = $renderElement->get("new_name");
            $userDir = $dir . $renderElement->get("user_dir");
            $userDir = str_replace("/./", "/", $userDir);
            $userDir = preg_replace('/\s/', '', $userDir);
            $fileName = $anim->get("name");
            $fileName = str_replace("\"", "", $fileName);
            $userLoginToken = User::find($anim->get("user_id"))->get("login_token");
            $userUniqueToken = User::find($anim->get("user_id"))->get("unique_token");
            $userId = $anim->get("user_id");
            $currentYear = $renderElement->get("date_add");

            echo "Start create script";

            exec("echo \"#!/bin/bash\" > {$dir}render/user_script_{$newName}.sh");
            $scriptFile = fopen("{$dir}render/user_script_{$newName}.sh", "w");
            fwrite($scriptFile, " mkdir -p {$dir}render/zdj_{$newName}");
            fwrite($scriptFile, " && phantomjs {$dir}runner.js {$animationId} {$newName} {$userLoginToken}");
            fwrite($scriptFile, " && cd {$dir}render/zdj_{$newName}/ && ffmpeg -framerate 60 -i frame_%00d.jpeg -c:v libx264 -preset slow -crf 18 -pix_fmt yuv420p -c:a libvo_aacenc -b:a 128k -id3v2_version 3 -metadata title=\"{$fileName}\" -metadata album_artist=\"Club Management Center\" -metadata author=\"Club Management Center\" -metadata year=\"{$currentYear}\" {$newName}.mp4");
            fwrite($scriptFile, " && mv {$dir}render/zdj_{$newName}/{$newName}.mp4 {$userDir}/{$newName}.mp4");
            fwrite($scriptFile, " && mv frame_0.jpeg {$userDir}/last.jpeg");
            fwrite($scriptFile, " && rm -rf {$dir}render/zdj_{$newName}");
            fwrite($scriptFile, " && rm -f {$dir}render/user_script_{$newName}.sh");
            fwrite($scriptFile, ' && curl -X POST --data "login_token=' . $userLoginToken . '&unique=' . $userUniqueToken . '&user_id=' . $userId . '&title=Serwer zakończył renderowanie filmu.&content=Twój projekt: <b>' . $fileName . '</b> właśnie został wyrenderowany i jest już dostępny do pobrania lub podglądu." https://api.centrumklubu.pl/notification');
            fwrite($scriptFile, ' && curl -X POST --data "login_token=' . $userLoginToken . '&unique=' . $userUniqueToken . '&user_id=' . $userId . '" https://api.centrumklubu.pl/animations/' . $animationId . '/saved');

            fclose($scriptFile);

            exec("chmod +x {$dir}render/user_script_{$newName}.sh");
            exec("chmod u+s {$dir}render/user_script_{$newName}.sh");
            exec("{$dir}render/user_script_{$newName}.sh > {$dir}render/out-{$animationId}.txt 2>{$dir}render/out2-{$animationId}.txt &");
        }
    }
}