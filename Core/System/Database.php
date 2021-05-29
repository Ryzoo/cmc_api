<?php

namespace Core\System;

use Core\System\Config;
use Core\System\Response;
use PDOException;

class Database
{
	private static $pdo;

    private function __construct(){}
    private function __clone(){}

    public static function getInstance(): \PDO
    {
        if(!self::$pdo){
            $config = (Config::config("environment") == "dev") ? Config::config("localDatabase") : Config::config("database");
            try {
                self::$pdo = new \PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';port=' . $config['port'], $config['username'], $config['password'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
                self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                Response::error($e, 500);
            }
        }

        return self::$pdo;
    }

    public static function getStats(?Request $request = null): array{
        $config = (Config::config("environment") == "dev") ? Config::config("localDatabase") : Config::config("database");

        $results = QueryBuilder::select(["TABLE_NAME","TABLE_ROWS","DATA_LENGTH"])
            ->from("information_schema.tables")
            ->where("TABLE_SCHEMA","LIKE",$config['dbname'])
            ->get($request);

        $size = 0;
        foreach ($results as &$item){
            $item["DATA_LENGTH"] = round($item["DATA_LENGTH"] / 1048576.0,2);
            $size += $item["DATA_LENGTH"];
        }

        return [
            "name" => $config['dbname'],
            "size" => round($size,2),
            "tables" => $results
        ];
    }

    public static function getTableName(): array
    {
        $config = (Config::config("environment") == "dev") ? Config::config("localDatabase") : Config::config("database");

        $results = QueryBuilder::select(["TABLE_NAME"])
            ->from("information_schema.tables")
            ->where("TABLE_SCHEMA","LIKE",$config['dbname'])
            ->get();

        $returnedTable = [];

        foreach($results as $row){
            $returnedTable[] = $row["TABLE_NAME"];
        }

        return $returnedTable;
    }

    public static function deleteTable(?string $tableName = null)
    {
        if(!$tableName) $tableName = join(', ',Database::getTableName());
        QueryBuilder::execQuery("DROP TABLE {$tableName}");
    }

    public static function deleteRow(?array $tableName = null)
    {
        if(!$tableName) $tableName = Database::getTableName();
        foreach($tableName as $table){
            QueryBuilder::execQuery("TRUNCATE TABLE {$table}");
        }
    }

    public static function runMigration($onlyNotExist = false)
    {
        $modelNamespace = "Core\\Model\\";
        $migrationTables = Config::config("migration");

        if(!$onlyNotExist) Database::deleteTable();

        if($migrationTables){
            if($onlyNotExist){
                $existTables = Database::getTableName();

                foreach($migrationTables as $table){
                    $tableName = call_user_func($modelNamespace.$table .'::getTableName');
                    if(array_search($tableName,$existTables) === false){
                        call_user_func($modelNamespace.$table .'::migrate');
                    }
                }

            }else{
                foreach($migrationTables as $table){
                    call_user_func($modelNamespace.$table .'::migrate');
                }
            }
        }else{
            Logger::getInstance()->error("Nie można wczytać konfiguracji migracji");
        }
    }

    public static function runCopy()
    {
        $config = (Config::config("environment") == "dev") ? Config::config("localDatabase") : Config::config("database");

        $DBUSER = $config['username'];
        $DBPASSWD = $config['password'];
        $DATABASE = $config['dbname'];

        $filename = "backup-" . date("d-m-Y") . ".sql.gz";
        $mime = "application/x-gzip";

        $cmd = "mysqldump -u $DBUSER --password=$DBPASSWD $DATABASE | gzip --best";

        ob_end_clean();
        http_response_code(200);
        header("Cache-Control: public");
        header( "Content-Type: " . $mime );
        header("Content-Transfer-Encoding: Binary");
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        passthru( $cmd );

        exit(0);
    }

    public static function reInitConnection()
    {
        $config = (Config::config("environment") == "dev") ? Config::config("localDatabase") : Config::config("database");
        try {
            self::$pdo = new \PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';port=' . $config['port'], $config['username'], $config['password'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Response::error($e, 500);
        }
    }

}