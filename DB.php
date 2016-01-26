<?php


define("DB_HOST", "");
define("DB_NAME", "");
define("DB_USER", "");
define("DB_PASS", "");


class DB {
    
    private static $instance;

    private $connection;
    
    private $query;
    
    private $param;
    
    private $sth;
    
    private function __construct() {
        $this->connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS );
        $this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);  
		$this->connection->exec("SET NAMES UTF8");
    }
    
    private function setQuery($q) {
        $this->query = $q;
    }
    
    private function setParam($p) {
        $this->param = $p;
    }
    
    private static function gi() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    
    public static function getConnection() {
        return self::gi()->connection;
    }
    
    public static function getStatement() {
        return self::gi()->sth;
    }
    
    public static function query($query = null, $param=array()) {
        self::gi()->setQuery($query);
        self::gi()->setParam($param);
        
        return self::gi();
    }
    
    private function exec() {
        $this->sth = $this->connection->prepare($this->query);
        return $this->sth->execute($this->param);
    }
    
    
    public function fetchAll() {
        $this->exec();
        return self::getStatement()->fetchAll( PDO::FETCH_ASSOC );
    }
    
    public function fetch() {
        $this->exec();
        return self::getStatement()->fetch(PDO::FETCH_ASSOC);
    }
    
    public function lastInsetId() {
        return self::getConnection()->lastInsertId();
    }
    
    public function execute() {
        return $this->exec();
    }
    
    public function insert($tableName, array $data) {
        
        $sql = "INSERT INTO `{$tableName}` ";
        
        $sql = $sql . '(`'.implode('`, `', array_keys($data)).'`) VALUES ( :' . implode(', :', array_keys($data)) .' )';
        
        $this->setParam($data);
        $this->setQuery($sql);
        
        return $this->exec();
    }
    
}
