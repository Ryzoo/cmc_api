<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 21.06.18
 * Time: 21:10
 */

namespace Core\Other\ConspectBuilder;

use Core\Model\Conspect;
use Core\Other\ConspectBuilder\Contract\IConspectTheme;
use Core\System\File;
use Core\System\FileManager;
use Exception;

class ConspectBuilder
{
    private $themeList;
    private $conspectModel;
    private $userUniqueToken;

    public function __construct(Conspect $conspectModel, string $userUniqueToken){
        $this->conspectModel = $conspectModel;
        $this->userUniqueToken = $userUniqueToken;
        $this->themeList = array();
    }

    public function addTheme(IConspectTheme $conspectTheme){
        array_push($this->themeList, $conspectTheme);
    }

    public function render():?int{

        if($this->conspectModel->pdf && is_numeric($this->conspectModel->pdf)) File::getById( (int) $this->conspectModel->pdf);

        $fileId = null;

        foreach($this->themeList as $theme){
            $theme->init($this->conspectModel,$this->userUniqueToken);
            $fileId = $theme->render();
        }

        return $fileId;
    }
}