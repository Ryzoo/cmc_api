<?php

namespace Core\System\Contract;

use Core\System\Request;

interface IFile{
    public function delete();
    public function move(string $newCatalog);
    public function getData();
    public function getName();
    public function getUrl();
    public function getPath();
}