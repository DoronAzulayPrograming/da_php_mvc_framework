<?php 

namespace DafCore\Db;


interface ITableQuery extends ITableQueryGenerator{
    public function execute();
}

interface ISelectQuery extends ISelectQueryGenerator{
    public function execute();
}

interface ITableQueryGenerator{
    function table($tableName);
    public function drop();
    public function addPrimaryKey($columnName);
    public function addColumn($name, $type, $size=null, $null=true, $default=null, $autoIncrement=false, $unique = false);
    public function addForeignKey($columnName, $refTableName, $refColumnName, $onDelete = null);
}

interface ISelectQueryGenerator{
    public function select($columns = "*");
    public function from($table, ?string $alias = null);
    public function where($column, $operator, $value, $conjunction = 'AND');
    public function orderBy($column, $direction = 'ASC');
    public function limit($limit);
    public function insert($data);
    public function update($data);
    public function join($table, $condition, $type = '', $join_model = null);
    public function delete();
}

class MySqlQueries implements ITableQuery, ISelectQuery{
    private $connection;
    private $table_name;
    protected $query;
    protected $query_closed = TRUE;
    protected $query_count = 0;
    private $tableQueryGenerator;
    public $selectQueryGenerator;
    private $baseContext;

    public function __construct(&$baseContext) {
        $this->baseContext = &$baseContext;
        $this->selectQueryGenerator = new MySQLSelectQueryGenerator();
        $this->tableQueryGenerator = new MySQLTableQueryGenerator();

        $this->connection = $this->baseContext->getConnection();
    }

    function __destruct() {
        $this->closeConnection();
    }
      
    private function closeConnection()
    {
        $this->baseContext->releaseConnection($this->connection);
    }

    function prepareQuery($queryString) {
        $query = $queryString;
        $query_params = [];
    
        preg_match_all('/:([a-zA-Z0-9_]+)/', $query, $matches);
    
        if (!empty($matches[1])) {
            $keys = $matches[1];
    
            foreach ($keys as $key) {
                $placeholder = ':' . $key;
                $query_params[$placeholder] = $query_params[$key];
                $query = str_replace("'" . $query_params[$key] . "'", $placeholder, $query);
            }
        }
    
        return [$query, $query_params];
    }

    public function customQuery($queryString){
        [$query, $query_params] = $this->prepareQuery($queryString);
        
        if(count($query_params) > 0){
            $this->query($query, ...$query_params);
        }else 
        return $this->query($query);
    }

    public function query($query): self
    {
        if (!$this->query_closed) {
            $this->query->closeCursor();
        }
        
        if ($this->query = $this->connection->prepare($query)) {
            $params = [];
            if (func_num_args() > 1) {
                $x = func_get_args();
                $params = array_slice($x, 1)[0];
            }

            // echo $query . "<br>";
            // var_dump($params) . "<br>";

            if(!empty($params)){
                // Bind the parameters
                foreach ($params as $key => $value) {
                    if (!$this->query->bindValue($key, $value)) {
                        echo $query . "<br>";
                        print_r($params) . "<br>";
                        $this->baseContext->error('Unable to Bind Values (check your params)', $this->query->errorInfo());
                    }
                }
            }
            if (!$this->query->execute()) {
                echo $query . "<br>";
                print_r($params) . "<br>";
                $this->baseContext->error('Unable to process MySQL query (check your params)', $this->query->errorInfo());
            }

            $this->query_closed = FALSE;
            $this->query_count++;
        } else {
            $this->baseContext->error(' -  Unable to prepare MySQL statement (check your syntax)', $this->connection->errorInfo());
        }
        return $this;
    }
  
    public function is_ready_for_execute(){
        return $this->tableQueryGenerator->is_ready() || $this->selectQueryGenerator->is_ready();
    }

    public function execute(): self
    {
        $query = null;
        $query_params = [];
        
        if($this->tableQueryGenerator->is_ready()){
            $query = $this->tableQueryGenerator->table($this->table_name)->generate();
            $this->tableQueryGenerator->clear();
        }

        
        if($this->selectQueryGenerator->is_ready()){
            $query_obj = $this->selectQueryGenerator->table($this->table_name)->generate();
            $query = $query_obj[0];
            $query_params = $query_obj[1];
            $this->selectQueryGenerator->clear();
        }

        // print_r($query_params);
        // echo $query . "<br><br>";
        
        if($query){
            if(empty($query_params)){
                $this->query($query);
            }
            else{
                $this->query($query, $query_params);
            }
        }

        return $this;
    }
  
    public function get_connection() :\PDO
    {
        return $this->connection;
    }
  
    public function pdo() :\PDOStatement
    {
        return $this->query;
    }

    public function fetchAs($class) {
        return $this->query->fetchObject($class);
    }

    public function fetchAllAs($class) : array{
        return $this->query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class);
    }
    
    public function table($table, ?string $alias = null) : self
    {
        if ($alias === null) {
            $this->table_name = $table;
        } else {
            $this->table_name = "$table AS $alias";
        }
        return $this;
    }

    public function table_exists($table_name) : bool
    {
        $this->query("SELECT 1 FROM information_schema.tables WHERE table_schema = :table_schema AND table_name = :table_name LIMIT 1;", ["table_schema"=>$this->baseContext->database_name, "table_name"=>$table_name]);
        $obj = $this->query->fetch(\PDO::FETCH_ASSOC);
        return count($obj) > 0;
    }

// <editor-fold defaultstate="collapsed" desc="TableQueryGenerator Region">
    public function drop($table_name = null) : ITableQuery
    {
        if($table_name != null)
            $this->tableQueryGenerator->drop_table($table_name);
        else $this->tableQueryGenerator->drop_table($this->table_name);
        return $this;
    }
    public function addColumn($name, $type, $size=null, $null=true, $default=null, $autoIncrement=false, $unique = false) : ITableQuery
    {
        $this->tableQueryGenerator->addColumn($name, $type, $size, $null, $default, $autoIncrement, $unique);
        return $this;
    }
  
    public function addPrimaryKey($columnName)  : ITableQuery
    {
        $this->tableQueryGenerator->addPrimaryKey($columnName);
        return $this;
    }
  
    public function addForeignKey($columnName, $refTableName, $refColumnName, $onDelete = null)  : ITableQuery
    {
        $this->tableQueryGenerator->addPrimaryKey($columnName, $refTableName, $refColumnName, $onDelete);
        return $this;
    }
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="QueryGenerator Region">

    public function exists() : ISelectQuery
    {
        $this->selectQueryGenerator->exists();
        return $this;
    }

    public function delete() : ISelectQuery
    {
        $this->selectQueryGenerator->delete();
        return $this;
    }

    public function select($columns = "*") : ISelectQuery
    {
        $this->selectQueryGenerator->select($columns);
        return $this;
    }

    public function from($table, ?string $alias = null)  : ISelectQuery
    {
        return $this->table($table, $alias);
    }

    public function where($column, $operator, $value, $conjunction = 'AND') : ISelectQuery
    {
        $this->selectQueryGenerator->where($column, $operator, $value, $conjunction);
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')  : ISelectQuery
    {
        $this->selectQueryGenerator->orderBy($column, $direction);
        return $this;
    }

    public function limit($limit) : ISelectQuery
    {
        $this->selectQueryGenerator->limit($limit);
        return $this;
    }

    public function insert($data)  : ISelectQuery
    {
        $this->selectQueryGenerator->insert($data);
        return $this;
    }

    public function update($data)  : ISelectQuery
    {
        $this->selectQueryGenerator->update($data);
        return $this;
    }

    public function join($table, $condition, $type = '', $join_model = null) : ISelectQuery
    {
        $this->selectQueryGenerator->join($table, $condition, $type, $join_model);
        return $this;
    }

// </editor-fold>
}

class MySQLTableQueryGenerator{
    private $tableName;
    private $drop = null;
    private $columns = [];
    private $primaryKeys = [];
    private $foreignKeys = [];

    public function is_ready(){
        if(
            empty($this->drop) &&
            empty($this->columns) &&
            empty($this->primaryKeys)
        ) return false;
        
        return true;
    }

    public function clear(){
        //$this->tableName = null;
        $this->drop = null;
        $this->columns = [];
        $this->primaryKeys = [];
        $this->foreignKeys = [];
    }

    public function table($tableName) {
        $this->tableName = $tableName;
        return $this;
    }

    public function drop(){
        $this->drop = true;
    }

    public function drop_table($table_name){
        $this->drop = true;
        $this->tableName = $table_name;
    }

    public function addColumn($name, $type, $size = null, $null = true, $default = null, $autoIncrement = false, $unique = false)
    {
        $column = [
            'name' => $name,
            'type' => $type,
            'size' => $size,
            'null' => $null,
            'default' => $default,
            'auto_increment' => $autoIncrement,
            'unique' => $unique
        ];
        $this->columns[] = $column;
    }

    public function addPrimaryKey($columnName) {
        $this->primaryKeys[] = $columnName;
    }

    public function addForeignKey($columnName, $refTableName, $refColumnName, $onDelete = null) {
        $foreignKey = [
            'column' => $columnName,
            'ref_table' => $refTableName,
            'ref_column' => $refColumnName,
            'on_delete' => $onDelete
        ];
        $this->foreignKeys[] = $foreignKey;
    }

    public function generate() {
        $query = "";
        if (!empty($this->drop)) {
            $query = "DROP TABLE IF EXISTS $this->tableName;";
        } else {
            $query = "CREATE TABLE IF NOT EXISTS `" . $this->tableName . "` (\n";
            foreach ($this->columns as $column) {
                $query .= "`" . $column['name'] . "` " . $column['type'];
                if ($column['size']) {
                    $query .= "(" . $column['size'] . ")";
                }
                if (!$column['null']) {
                    $query .= " NOT NULL";
                }
                if ($column['default'] !== null) {
                    $query .= " DEFAULT " . $column['default'] . "";
                }
                if ($column['auto_increment']) {
                    $query .= " AUTO_INCREMENT";
                }
                if ($column['unique']) {
                    $query .= " UNIQUE";
                }
                $query .= ",\n";
            }
            if (!empty($this->primaryKeys)) {
                $query .= "PRIMARY KEY (`" . implode("`, `", $this->primaryKeys) . "`),\n";
            }
            foreach ($this->foreignKeys as $foreignKey) {
                $query .= "CONSTRAINT `fk_" . $foreignKey['column'] . "` FOREIGN KEY (`" . $foreignKey['column'] . "`) ";
                $query .= "REFERENCES `" . $foreignKey['ref_table'] . "` (`" . $foreignKey['ref_column'] . "`)";
                if ($foreignKey['on_delete']) {
                    $query .= " ON DELETE " . $foreignKey['on_delete'];
                }
                $query .= ",\n";
            }
            $query = rtrim($query, ",\n"); // remove the last comma and newline
            $query .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        }
        return $query;
    }
}

class MySQLSelectQueryGenerator
{
    protected $need_to_close = false;
    protected $isExists = false;
    protected $isDelete = false;
    protected $table;
    protected $select = [];
    protected $join = [];
    protected $where = [];
    protected $order = [];
    protected $limit;
    protected $insertData = [];
    private $updateData = [];
    private $bindings = [];

    private $query_params = [];

    public function is_ready(){
        if(
            ($this->isExists && !empty($this->where))  ||
            ($this->isDelete && !empty($this->where))  ||
            !empty($this->select) ||
            !empty($this->insertData) ||
            !empty($this->updateData)
        ) return true;
        
        return false;
    }

    public function clear()
    {
        //$this->table = null;
        $this->select = [];
        $this->where = [];
        $this->order = [];
        $this->limit = null;
        $this->insertData = [];
        $this->updateData = [];
        $this->join = [];
        $this->need_to_close = false;
        $this->isExists = false;
        $this->isDelete = false;
        $this->bindings = [];
        $this->query_params = [];
        return $this;
    }

    public function table($table, ?string $alias = null)
    {
        if ($alias === null) {
            $this->table = $table;
        } else {
            $this->table = "$table AS $alias";
        }
        return $this;
    }

    public function exists(){
        $this->isExists = true;
    }

    public function delete(){
        $this->need_to_close = true;
        $this->isDelete = true;
    }

    public function from($table, ?string $alias = null)
    {
        return $this->table($table, $alias);
    }

    public function select($columns = "*")
    {
        $exist = array_filter($this->select, function($col) use ($columns){
            return $col === $columns;
        });
        if (!empty($exist)) {
            return $this;
        }

        if (is_array($columns)) {
            $this->select = $columns;
        } else {
            $this->select[] = $columns;
        }
        return $this;
    }

    public function where($column, $operator, $value, $conjunction = 'AND')
    {
        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'conjunction' => $conjunction
        ];
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->order[] = [
            'column' => $column,
            'direction' => $direction
        ];
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function insert($data)
    {
        $this->need_to_close = true;
        $this->insertData = $data;
        return $this;
    }

    public function update($data)
    {
        $this->need_to_close = true;
        $this->updateData = $data;
        return $this;
    }

    public function join($table, $condition, $type = '', $join_model = null)
    {
        $this->join[] = [$table, $condition, $type, $join_model];
        return $this;
    }

    function insert_data_logic() {
        $columns = [];
        $values = [];
        $data = is_object($this->insertData) ? get_object_vars($this->insertData) : $this->insertData;
    
        foreach ($data as $key => $value) {
            $columns[] = $key;
            $values[] = ':' . $key;
            $this->query_params[':' . $key] = $value;
        }
    
        $columnsStr = implode(",", $columns);
        $valuesStr = implode(",", $values);
        $query = "INSERT INTO " . $this->table . " (" . $columnsStr . ") VALUES (" . $valuesStr . ")";
        
        return $query;
    }

    function exists_in_where($column, $value){
        $found = false;
        $eq = false;

        foreach ($this->where as $key => $item) {
            if($item['column'] == $column){
                $found = true;
                if($item['value'] == $value)
                    $eq = true;
                break;
            }
        }

        if($found && $eq) 
            return 1;
        else if($found)
            return -1;
        else 0;
    }

    function update_data_logic() {
        $query = "UPDATE " . $this->table . " SET ";
    
        $updateStr = "";
        foreach ($this->updateData as $column => $value) {
            $res = $this->exists_in_where($column , $value);
            if($res == -1){
                $updateStr .= $column . " = :new" . ucfirst($column) . ",";
                $this->query_params[':new' . ucfirst($column)] = $value;
            }

            if($res == -1 || $res == 1) continue;

            $updateStr .= $column . " = :" . $column . ",";
            $this->query_params[':' . $column] = $value;
        }
        $updateStr = rtrim($updateStr, ",");
        $query .= $updateStr;
    
        return $query;
    }

    function join_logic($query)
    {
        if (!empty($this->join)) {
            foreach ($this->join as $join) {
                list($table, $condition, $type, $join_model) = $join;
                $query .= " $type JOIN $table ON $condition";
            }
        }

        return $query;
    }

    function where_logic($query){
        if (!empty($this->where)) {
            $query .= " WHERE ";
            foreach ($this->where as $i => $clause) {
                $query .= $clause['column'] . " " . $clause['operator'] . " :".$clause['column'];
                $this->query_params[':'.$clause['column']] = $clause['value'];
                if ($i < count($this->where) - 1) {
                    $query .= " " . $clause['conjunction'] . " ";
                }
            }
        }
        return $query;
    }

    function order_logic($query){
        if (!empty($this->order)) {
            $query .= " ORDER BY ";
            foreach ($this->order as $i => $clause) {
                $query .= $clause['column'] . " " . $clause['direction'];
                if ($i < count($this->order) - 1) {
                    $query .= ", ";
                }
            }
        }
        return $query;
    }

    function limit_logic($query){
        if (!empty($this->limit)) {
            $query .= " LIMIT :limit";
            $this->query_params['limit'] = $this->limit;
        }
        return $query;
    }

    public function generate()
    {
        $query = "";
        $this->query_params = array();
    
        if (!empty($this->insertData)) {
            $query = $this->insert_data_logic($query);

        } else if ($this->isExists) {

            $query = "SELECT COUNT(*) FROM $this->table";
            $query = $this->where_logic($query);
            
        } else if ($this->isDelete) {

            $query = "DELETE FROM $this->table";
            $query = $this->where_logic($query);
            
        } else if (!empty($this->updateData)) {

            $query = $this->update_data_logic($query);
            $query = $this->where_logic($query);

        } else {
            $query = "SELECT ";
            
            if (empty($this->select)) {
                $query .= "*";
            } else {
                
                // if(!empty($this->join)){
                //     $query .= "$this->table.*,";
                //     foreach ($this->join as $join) {
                //         list($table, $condition, $type, $join_model) = $join;
                //         $model = new $join_model();
                //         $properties = get_object_vars($model);
                //         $prefixedProperties = array_map(function ($key) use ($table) {
                //             return $table .'.'. $key . ' AS '. $table . '_' . $key;
                //         }, array_keys($properties));
                //         $query .= implode(",", $prefixedProperties);
                //     }
                // } else
                $query .= implode(",", $this->select);
            }
    
            $query .= " FROM " . $this->table;
    
            $query = $this->join_logic($query);
            $query = $this->where_logic($query);
            $query = $this->order_logic($query);
            $query = $this->limit_logic($query);

        }

        // print_r($this->query_params);
        //echo $query."<br><br>";

        return array($query, $this->query_params);
    }

    public function __toString(){
        return $this->generate()[0];
    }

}

class BaseContext extends MySqlQueries{
    public $database_name;

    private $dsn;
    private $username;
    private $password;
    private $options;
    private $maxConnections;
    private $connectionPool = [];

    public static $show_errors = TRUE;
  
    public function __construct($host, $port, $database, $username, $password, $maxConnections = 10) {
      $this->dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
      $this->database_name = $database;
      $this->username = $username;
      $this->password = $password;
      $this->options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
      ];
      $this->maxConnections = $maxConnections;
      parent::__construct($this);
    }


    public function error($error, $obj = null)
    {
        if (self::$show_errors) {
            die($error . " - " . implode("", $obj));
        }
    }
  
    public function getConnection() {
        
      if (count($this->connectionPool) < $this->maxConnections) {
        $connection = new \PDO($this->dsn, $this->username, $this->password);
        
        if(!$connection){
            $this->error("Connection failed: ", $connection->errorInfo());
        }
        else $this->connectionPool[] = $connection;
      } else {
        $connection = array_shift($this->connectionPool);
      }
      return $connection;
    }
  
    public function releaseConnection($connection) {
      if (count($this->connectionPool) < $this->maxConnections) {
        $this->connectionPool[] = $connection;
      } else {
        $connection = null;
      }
    }
  
}

interface IDbSetTableQuery{
    public function execute();
    public function drop();
    public function addPrimaryKey($columnName);
    public function addColumn($name, $type, $size=null, $null=true, $default=null, $autoIncrement=false, $unique = false);
    public function addForeignKey($columnName, $refTableName, $refColumnName, $onDelete = null);
}

interface IDbSetSelectQueryy{
    public function execute();
    public function select($columns = "*");
    public function where($column, $operator, $value, $conjunction = 'AND');
    public function orderBy($column, $direction = 'ASC');
    public function limit($limit);
    public function insert($data);
    public function update($data);
    public function join($table, $condition, $type = '', $join_model = null);
    public function delete();
}

class DbSet implements IDbSetTableQuery, IDbSetSelectQueryy{
    private $table_name;
    private $table_class;
    private $mySqlQueries;

    public function __construct(&$baseContext ,$table_class, $table_name)
    {
        $this->table_class = $table_class;
        $this->table_name = $table_name;
        $this->mySqlQueries = new MySqlQueries($baseContext);
        $this->mySqlQueries->table($this->table_name);
    }

    public function customQuery($query): self
    {
        $this->mySqlQueries->customQuery($query);
        return $this;
    }

    public function execute(): self
    {
        $this->mySqlQueries->execute();
        return $this;
    }

    public function pdo() : \PDOStatement
    {
        return $this->mySqlQueries->pdo();
    }

    public function lastInsertId()
    {
        return $this->mySqlQueries->get_connection()->lastInsertId();
    }
    
    public function many() : array{
        if(!$this->mySqlQueries->is_ready_for_execute())
        {
            $this->select()->execute();
        } else $this->execute();

        return $this->mySqlQueries->fetchAllAs($this->table_class);
    }
    
    public function single() {
        $this->execute();
        return $this->mySqlQueries->fetchAs($this->table_class);
    }
    
    public function first() {
        if(!$this->mySqlQueries->is_ready_for_execute())
        {
            $this->select()->execute();
        } else $this->execute();
        return $this->mySqlQueries->fetchAs($this->table_class);
    }
    
// <editor-fold defaultstate="collapsed" desc="TableQueryGenerator Region">
    public function drop() : IDbSetTableQuery
    {
        $this->mySqlQueries->drop($this->table_name);
        return $this;
    }
    public function addColumn($name, $type, $size=null, $null=true, $default=null, $autoIncrement=false, $unique = false) : IDbSetTableQuery
    {
        $this->mySqlQueries->addColumn($name, $type, $size, $null, $default, $autoIncrement, $unique);
        return $this;
    }

    public function addPrimaryKey($columnName)  : IDbSetTableQuery
    {
        $this->mySqlQueries->addPrimaryKey($columnName);
        return $this;
    }

    public function addForeignKey($columnName, $refTableName, $refColumnName, $onDelete = null)  : IDbSetTableQuery
    {
        $this->mySqlQueries->addPrimaryKey($columnName, $refTableName, $refColumnName, $onDelete);
        return $this;
    }
// </editor-fold>


// <editor-fold defaultstate="collapsed" desc="QueryGenerator Region">

    public function existsWhere($column, $operator, $value) : bool
    {
        $count = $this->mySqlQueries->exists()->where($column, $operator, $value)->execute()->pdo()->fetchColumn();
        return $count > 0;
    }

    public function exists() : bool
    {
        $count = $this->mySqlQueries->exists()->execute()->pdo()->fetchColumn();
        return $count > 0;
    }

    public function delete() : IDbSetSelectQueryy
    {
        $this->mySqlQueries->delete();
        return $this;
    }

    public function select($columns = "*") : IDbSetSelectQueryy
    {
        $this->mySqlQueries->select($columns);
        return $this;
    }

    public function where($column, $operator, $value, $conjunction = 'AND') : IDbSetSelectQueryy
    {
        $this->mySqlQueries->select();
        $this->mySqlQueries->where($column, $operator, $value, $conjunction);
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')  : IDbSetSelectQueryy
    {
        $this->mySqlQueries->orderBy($column, $direction);
        return $this;
    }

    public function limit($limit) : IDbSetSelectQueryy
    {
        $this->mySqlQueries->limit($limit);
        return $this;
    }

    public function insert($data)  : IDbSetSelectQueryy
    {
        if(!$this->mySqlQueries->table_exists($this->table_name)) return $this;

        $this->mySqlQueries->table($this->table_name);
        $this->mySqlQueries->insert($data);
        return $this;
    }

    public function update($data)  : IDbSetSelectQueryy
    {
        $this->mySqlQueries->update($data);
        return $this;
    }
    
    public function join($table, $condition, $type = '', $join_model = null) : IDbSetSelectQueryy{
        $this->mySqlQueries->join($table, $condition, $type, $join_model);
        return $this;
    }
    
// </editor-fold>
}
