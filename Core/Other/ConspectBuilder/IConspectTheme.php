<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 21.06.18
 * Time: 21:23
 */

namespace Core\Other\ConspectBuilder;


use Core\Models\Conspect;

interface IConspectTheme
{
    public function saveToFile( string $pathName );
    public function render();
    public function init(Conspect $conspectModel, string $userUniqueToken);
}