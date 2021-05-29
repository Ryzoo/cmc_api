<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 28.09.18
 * Time: 06:43
 */

namespace Core\Controller;


use Core\System\Database;
use Core\System\Request;
use Core\System\Response;

class DatabaseController
{
    public function deleteTable(Request $request)
    {
        Database::deleteTable();
        Response::json(true);
    }

    public function deleteTableRow(Request $request, string $tableName)
    {
        Database::deleteRow([$tableName]);
        Response::json(true);
    }

    public function deleteRow(Request $request)
    {
        Database::deleteRow();
        Response::json(true);
    }

    public function runMigration(Request $request)
    {
        $onlyNonExist = $request->get("onlyNonExist", true) !== null;

        Database::runMigration($onlyNonExist);
        Response::json(true);
    }

    public function runCopy(Request $request)
    {
        Database::runCopy();
    }

}