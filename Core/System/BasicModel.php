<?php

namespace Core\System;

use Core\System\QueryBuilder;

class BasicModel{
    public static function getFields( $fields = null, $countable = false ){
        $allFields = $fields ?? static::$fields;
        $tableName = static::$table;
        $return = [];
        foreach ($allFields as $value) {
            $return[] = $countable ? "COUNT(".$tableName.".".$value.")": $tableName.".".$value;
        }
        return $return;
    }

    public static function all(?Request $request = null): array
    {
        $allFields = static::$fields;
        $tableName = static::$table;
        unset($allFields['table']);
        unset($allFields['relations']);

        $builder = new QueryBuilder();
        $builder->setClassName( get_called_class() );
        $builder->addSelect($allFields);
        $builder->addFrom($tableName);
        return $builder->get($request);
    }

    public static function migrate()
    {
        $className = get_called_class();
        $allFields = static::$fields;
        $tableName = static::$table;
        $fieldType = static::$fieldsType;

        if(!$tableName){
            $error = "Problem przy migracji z klasą: {$className} - brak nazwy tabeli";
            Logger::getInstance()->error($error);
            Response::error($error);
        }

        if(count($allFields) !== count($fieldType)){
            $error = "Problem przy migracji z klasą: {$className} - ilość nazw oraz typów kolumn jest różna.";
            Logger::getInstance()->error($error);
            Response::error($error);
        }
        $columnString = "";

        foreach ($allFields as $key => $field) {
            $columnString .= ($key===0?"":",")."`{$field}` {$fieldType[$key]}";
        }

        $prepareQueryString = "CREATE TABLE `{$tableName}` ({$columnString}) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_polish_ci";

        QueryBuilder::execQuery($prepareQueryString);
    }

    public function get($param){
        $allFields = $this::$fields;

        unset($allFields['table']);
        unset($allFields['relations']);

        if(in_array($param,$allFields)){
            return $this->$param;
        }
        throw new \Exception("Klasa ".get_called_class()." nie posiada parametru ".$param);
    }

    public function update(array $fields){
        $allFields = static::$fields;
        unset($allFields['table']);
        unset($allFields['relations']);

        foreach ($fields as $index => $item) {
            if(in_array($index,$allFields)){
                $this->$index = $item;
            }
        }
        $this->save();
    }

    public static function find(int $columnValue, bool $withRel = true,string $findColumn = 'id') {
        $allFields = static::$fields;
        $tableName = static::$table;
        unset($allFields['table']);
        unset($allFields['relations']);
        $allFieldsString = implode(', ',$allFields);

        $query = Database::getInstance()->prepare("SELECT {$allFieldsString} FROM {$tableName} WHERE {$findColumn}=?");
        $query->execute([$columnValue]);
        $result = $query->fetch();
        $query->closeCursor();
        unset($query);

        if(!$result) return null;

        $objectName = get_called_class();
        $object = new $objectName;
        foreach ($allFields as $value) {
            $object->$value = $result[$value] ?? NULL;
        }
        if($withRel){
            foreach (static::$relations as $value) {
                $className = $value[0];
                $classNameFull = "Core\\Model\\".$className;
                $foreignKeyName = $value[1];
                $attr = $value[3];
                $object->$attr = ($classNameFull)::find($object->get($foreignKeyName),true,$value[2]);
            }
        }

        return $object;
    }

    public function loadRelation()
    {
        foreach (static::$relations as $value) {
            $className = $value[0];
            $classNameFull = "Core\\Model\\".$className;
            $foreignKeyName = $value[1];
            $attr = $value[3];
            $this->$attr = ($classNameFull)::find($this->get($foreignKeyName),true,$value[2]);
        }
    }

    public function map(Array $fields){
        $allFields = static::$fields;

        foreach ($allFields as $value) {
            $this->$value = $fields[$value] ?? NULL;
        }

        foreach (static::$relations as $value) {
            $className = $value[0];
            $classNameFull = "Core\\Model\\".$className;
            $foreignKeyName = $value[1];
            $attr = $value[3];
            $this->$attr = ($classNameFull)::find($this->get($foreignKeyName),true,$value[2]);
        }
    }

    protected function save():bool{
        if(isset($this->id)) return $this->updateModel();
        else return $this->createModel();
    }

    protected function updateModel():bool{
        $allFields = static::$fields;
        foreach ($allFields as $i => $value) {
            if($value == "id") unset($allFields[$i]);
            else $allFields[$i] = $value."=?";
        }

        $tableName = static::$table;
        $allFieldsString = implode(', ',$allFields);
        unset($allFields['table']);
        unset($allFields['relations']);

        $query = Database::getInstance()->prepare("UPDATE {$tableName} SET {$allFieldsString} WHERE id=?");
        $objectArray = (array)$this;
        unset($objectArray['id']);
        foreach ($objectArray as $i => $value) {
            if(gettype($objectArray[$i]) == "object"){
                unset($objectArray[$i]);
            }
        }

        $objectArray = array_values(array_merge($objectArray,[$this->id]));
        $result = $query->execute($objectArray);
        $query->closeCursor();
        unset($query);

        $this->prepare();

        return $result;
    }

    public static function getTableName():string
    {
        return static::$table;
    }

    protected function createModel():bool{
        $allFields = static::$fields;

        unset($allFields['table']);
        unset($allFields['relations']);

        $propString = "";

        foreach ($allFields as $i => $value) {
            if($value == "id") {
                unset($allFields[$i]);
                continue;
            }
            $allFields[$i] = "`".$allFields[$i]."`";
            if(strlen($propString) === 0) $propString .= "?";
            else $propString .= ", ?";
        }

        $tableName = static::$table;
        $allFieldsString = implode(', ',$allFields);

        $query = Database::getInstance()->prepare("INSERT INTO `{$tableName}` ({$allFieldsString}) VALUES ({$propString})");

        $objectArray = (array)$this;
        unset($objectArray['id']);

        $objectArray = array_values($objectArray);
        $result = $query->execute($objectArray);

        $query->closeCursor();

        $this->id = Database::getInstance()->lastInsertId();
        $this->prepare();

        unset($query);

        return $result;
    }

    public static function remove($id)
    {
        $tableName = static::$table;

        $builder = new QueryBuilder();
        $builder->setClassName(get_called_class());
        $builder->addFrom($tableName);

        return $builder->delete($id);
    }

    public static function where(String $param, String $word, $value): QueryBuilder
    {
        $allFields = static::$fields;
        $tableName = static::$table;
        unset($allFields['table']);
        unset($allFields['relations']);

        $builder = new QueryBuilder();
        $builder->setClassName( get_called_class() );
        $builder->addSelect($allFields);
        $builder->addFrom($tableName);
        $builder->where($param, $word, $value);

        return $builder;
    }

    public function delete(): bool
    {
        $tableName = static::$table;

        $builder = new QueryBuilder();
        $builder->setClassName(get_called_class());
        $builder->addFrom($tableName);

        return $builder->delete($this->id);
    }

    protected function prepare(){
        return true;
    }

    public static function create(array $field)
    {
        $model = new static;

        $allFields = static::$fields;
        unset($allFields['table']);
        unset($allFields['relations']);

        foreach ($field as $index => $item) {
            if(in_array($index,$allFields)){
                $model->$index = $item;
            }
        }
        $model->save();

        return $model;
    }
}