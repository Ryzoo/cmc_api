<?php
namespace Core\System;

use Core\System\MemcachedController;
use PDO;
use React\Cache\ArrayCache;

class QueryBuilder{
    private $className = "";
    private $select = [];
    private $from = [];
    private $where = [];
    private $join = [];
    private $groupBy = [];
	private $orderBy = [];
	private $updateValue = [];
    private $isUpdate = false;
    private $limit = NULL;
    private $queryString = "";
    private $descend = false;
    private $createNewObject = false;

    public function setClassName(String $name){
        $this->className = $name;
    }

    public function addSelect($fields){
        if(is_array($fields)){
            foreach ($fields as $value) {
                $this->select[] = $value;
            }
        }else{
            $this->select[] = $fields;
        }
        return $this;
    }

	public function addUpdateValue($fields){

		foreach ($fields as $value) {
			$this->updateValue[] = $value;
		}

		return $this;
	}

    public function addFrom($fields){
        if(is_array($fields)){
            foreach ($fields as $value) {
                $this->from[] = $value;
            }
        }else{
            $this->from[] = $fields;
        }
        return $this;
    }

    public function from($fields): QueryBuilder{
        $this->addFrom($fields);
        return $this;
    }

	public function in($fields): QueryBuilder{
		$this->addFrom($fields);
		return $this;
	}

    public function addWhere($fields){
        if(is_array($fields)){
            foreach ($fields as $value) {
                $this->where[] = $value;
            }
        }else{
            $this->where[] = $fields;
        }
        return $this;
    }

    public function join($fields){
        if(is_array($fields)){
            foreach ($fields as $value) {
                $this->join[] = $value;
            }
        }else{
            $this->join[] = $fields;
        }
        return $this;
    }

    public function group($fields){
        if(is_array($fields)){
            foreach ($fields as $value) {
                $this->groupBy[] = $value;
            }
        }else{
            $this->groupBy[] = $fields;
        }
        return $this;
    }

    public function order($fields){
        if(is_array($fields)){
            foreach ($fields as $value) {
                $this->orderBy[] = $value;
            }
        }else{
            $this->orderBy[] = $fields;
        }
        return $this;
    }

    public static function select($fields): QueryBuilder{
        $query = new QueryBuilder();
        $query->addSelect($fields);
        $query->createNewObject = true;
        return $query;
    }

	public static function update($fields){
		$query = new QueryBuilder();
		$query->addUpdateValue($fields);
		$query->isUpdate = true;
		return $query;
	}

    public function where(String $param, String $word, String $value): QueryBuilder{
        if("like" === strtolower($word)){
            $word = "LIKE";
            $this->addWhere("{$param} {$word} '{$value}'");
        }else{
            $this->addWhere("{$param} {$word} {$value}");
        }
        return $this;
    }

    public function whereOr(String $param, String $word, String $value): QueryBuilder{
        if("like" === strtolower($word)){
            $word = "LIKE";
            $this->addWhere("{$param} {$word} '{$value}' OR");
        }else{
            $this->addWhere("{$param} {$word} {$value} OR");
        }
        return $this;
    }

    public function groupBy($fields): QueryBuilder{
        $this->group($fields);
        return $this;
    }

    public function orderBy($fields, $isDesc = false): QueryBuilder{
        $this->descend = $isDesc;
        $this->order($fields);
        return $this;
    }

    public function joinOn(String $object, String $key, String $foreignKey): QueryBuilder{
        $this->join("JOIN {$object} ON {$key}={$foreignKey}");
        return $this;
    }

    public function get(?Request $request = null): array{
        if(!is_null($request)) $this->useFilter($request);
        return $this->execute();
    }

    private function buildQueryString($isDebug = false){

    	if($this->isUpdate){
			$queryString = "UPDATE";

			foreach ($this->from as $key => $value) {
				$queryString .= ($key != 0 ? " ," : " ").$value;
			}

			$queryString .= " SET";

			foreach ($this->updateValue as $key => $value) {
				$queryString .= ($key != 0 ? " ," : " ").$value["name"]." = ".$value["value"];
			}

			if(count($this->where)>0){
                $queryString .= " WHERE";
                foreach ($this->where as $key => $value) {
                    if(strpos($value,"OR") !== false){
                        $queryString .= ($key != 0 ? " OR " : " ").str_replace("OR","",$value);
                    }else{
                        $queryString .= ($key != 0 ? " AND " : " ").$value;
                    }
                }
			}

			$this->queryString = $queryString;
		}else{
			$queryString = "SELECT";

			foreach ($this->select as $key => $value) {
				$queryString .= ($key != 0 ? " ," : " ").$value;
			}

			$queryString .= " FROM";

			foreach ($this->from as $key => $value) {
				$queryString .= ($key != 0 ? " ," : " ").$value;
			}

			if(count($this->join)>0){
				foreach ($this->join as $key => $value) {
					$queryString .= " ".$value;
				}
			}

			if(count($this->where)>0){
				$queryString .= " WHERE";
                foreach ($this->where as $key => $value) {
                    if(strpos($value,"OR") !== false){
                        $queryString .= ($key != 0 ? " OR " : " ").str_replace("OR","",$value);
                    }else{
                        $queryString .= ($key != 0 ? " AND " : " ").$value;
                    }
                }
            }

			if(count($this->groupBy)>0){
				$queryString .= " GROUP BY ";
				foreach ($this->groupBy as $key => $value) {
					$queryString .= ($key != 0 ? " ," : " ").$value;
				}
			}

			if(count($this->orderBy)>0){
				$queryString .= " ORDER BY ";
				foreach ($this->orderBy as $key => $value) {
					$queryString .= ($key != 0 ? " ," : " ").$value;
				}
				if($this->descend){
					$queryString .= " DESC";
				}
			}

			if($this->limit != NULL){
				$queryString .= " LIMIT {$this->limit}";
			}

			$this->queryString = $queryString;
		}
		if($isDebug) return $this->queryString;
    }

    public function delete(int $id):bool{
        $query = Database::getInstance()->prepare( "DELETE FROM {$this->from[0]} WHERE id=:id" );
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();
        unset($query);
        return true;
    }

    public function exec(): array{
		return $this->execute();
	}

	public function debug(){
    	var_dump($this->buildQueryString(true));
    	die();
	}

    public static function execQuery(string $queryString)
    {
        $response = null;
        $query = Database::getInstance()->prepare( $queryString );
        $query->execute();
        try{
            $response = $query->fetchAll();
        }catch (\Exception $e){

        }
        $query->closeCursor();
        return $response;
	}

    private function execute(): array{
        $this->buildQueryString();
        $key = md5($this->queryString);
        $isDebug = Config::config("environment") === 'dev';
        $result = $this->sendQuery($key,$isDebug);
        $arrayToReturn = [];

		if(!$this->isUpdate && $result){
			if(!$this->createNewObject){
				foreach ($result as $key => $value) {
                    if($this->className){
                        $object = new $this->className;
                        $object->map($value);
                    }else{
                        $object = [];
                        $object[$key] = $value;
                    }
                    $arrayToReturn[] = $object;
				}
			}else{
				if(isset($result) && isset($result[0]) && is_array($result[0])){
					$arrayToReturn = $result;
				}else{
					foreach ($result as $key => $value) {
                        $object = [];
                        $object[$key] = $value;
						$arrayToReturn[] = $object;
					}
				}
			}
		}

        return $arrayToReturn;
    }

    private function sendQuery($key,$debug = false){
        $result = null;
        $query = Database::getInstance()->prepare( $this->queryString );

        if(!$debug){
            $memcache = MemcachedController::getInstance();
            if($memcache->checkKeyExist($key)){
                $result = $memcache->get($key);
            }else{
                $query->execute();
                if(!$this->isUpdate){
                    $result = $query->fetchAll();
                    $memcache->save($key,$result);
                }
            }
        }else{
            $query->execute();
            if(!$this->isUpdate){ $result = $query->fetchAll(); }
        }

        $query->closeCursor();
        unset($query);

        return $result;
    }

    private function useFilter(Request $request){

        // sorting --- url?sort=date|desc,name|asc
        //
        $sort = $request->get("sort",true);
        if($sort){
            $sortList = explode(",",$sort);
            foreach ($sortList as $item) {
                $itemString = explode("|",strip_tags($item));
                $isDesc = isset($itemString[1]) && $itemString[1] === 'desc'? true : false;
                $this->orderBy($itemString[0],$isDesc);
            }
        }

        // pagination

        // limit  --- url?limit=10
        $limit = $request->get("limit",true);
        if($limit){
            $this->limit = strip_tags($limit);
        }

        // filter  --- filter?name|=|default,price|>|6
        $filter = $request->get("filter",true);

        if($filter){
            $filterList = explode(",",$filter);
            foreach ($filterList as $key => $item) {
                $itemString = explode("|",strip_tags($item));
                if(count($itemString) === 3){
                    $this->where($itemString[0],$itemString[1],$itemString[2]);
                }
            }
        }

    }
}