<?php


define("DB_HOST", "");
define("DB_NAME", "");
define("DB_USER", "");
define("DB_PASS", "");


class DB {

    /**
     * @var DB
     */
    private static $instance;

    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var string
     */
    private $query;
    
    /**
     * @var array
     */
    private $param;

    /**
     * @var PDOStatement
     */
    private $sth;

    /**
     * Constructor
     */
    private function __construct() {
        $this->connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS );
        $this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
                    $this->connection->exec("SET NAMES UTF8");
    }

    /**
     * Sets query that will be executed 
     * @param string $q - query
     */
    private function setQuery($q) {
        $this->query = $q;
    }

    /**
     * Sets parrams that will be binded 
     * @param array $p - param
     */
    private function setParam($p) {
        $this->param = $p;
    }

    /**
     * Return instance of DB
     * @return self
     */
    private static function gi() {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Returns PDO connection 
     * @return PDO
     */
    public static function getConnection() {
        return self::gi()->connection;
    }

    /**
     * Returns PDO statement 
     * @return PDOStatement
     */
    public static function getStatement() {
        return self::gi()->sth;
    }

    /**
     * Static method for executing 
     * query and params 
     * @return self
     */
    public static function query($query = null, $param=array()) {
        self::gi()->setQuery($query);
        self::gi()->setParam($param);

        return self::gi();
    }

    /**
     * Calls procedure 
     */
    public static function call($procedure) {
      self::gi()->setQuery("CALL {$procedure};");
      return self::gi();
    }

    /**
     * Executes statement 
     * @return bool 
     */
    private function exec() {
        $this->sth = $this->connection->prepare($this->query);
        return $this->sth->execute($this->param);
    }
  
    /**
     * Fetches result - multiple rows
     * @return array - multidimensional array 
     */
    public function fetchAll() {
        $this->exec();
        return self::getStatement()->fetchAll( PDO::FETCH_ASSOC );
    }

    /**
     * Fetches one row
     * @return array 
     */
    public function fetch() {
        $this->exec();
        return self::getStatement()->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Return last inserted id 
     */
    public function lastInsertId() {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Executes query
     */
    public function execute() {
        return $this->exec();
    }

    /**
     * Selects data from db
     * @return array
     */
    public function select($tableName, array $where = [], $one = false) {
        $sql = "SELECT * FROM `{$tableName}`";

        if (!empty($where)) {
            $sql .= " WHERE ";

            foreach ($where as $key => $val) {
                $sql .= "`$key` = :$key AND ";
            }

            $sql = substr($sql, 0, -4);
        }

        $this->setParam($where);
        $this->setQuery($sql);

        return $one ? $this->fetch() : $this->fetchAll();
    }

    /**
     * Replaces data in db
     * @return bool 
     */
    public function replace($tableName, array $data) {

         $sql = "REPLACE INTO `{$tableName}` ";

         $sql = $sql . '(`'.implode('`, `', array_keys($data)).'`) VALUES ( :' . implode(', :', array_keys($data)) .' )';

         $this->setParam($data);
         $this->setQuery($sql);

         return $this->exec();
     }
     
     /**
      * Inserts data in db
      * @return bool 
      */
     public function insert($tableName, array $data) {

        $sql = "INSERT INTO `{$tableName}` ";

        $sql = $sql . '(`'.implode('`, `', array_keys($data)).'`) VALUES ( :' . implode(', :', array_keys($data)) .' )';

        $this->setParam($data);
        $this->setQuery($sql);

        return $this->exec();
     }
     
     /**
      * Updates data in db
      * @return bool 
      */
      public function update($table, array $what, array $where = []) {
          $sql = "UPDATE `$table` SET ";

          foreach ($what as $key => $val) {
              $sql .= "`{$key}` = :{$key} , ";
          }

                 $sql=substr($sql, 0, -2);

          if (!empty($where)) {
              $sql .= " WHERE ";

              foreach ($where as $key => $val) {

                  $originalKey = $key;

                  if (isset($what[$key])) {
                      unset($what[$key]);
                      $key = "x".$key.mt_rand();
                      $what[$key] = $val;
                  }

                  $sql .= "`$originalKey` = :$key AND ";
              }

              $sql = substr($sql, 0, -4);
          }

          $this->setParam(array_merge($what, $where));
          $this->setQuery($sql);

          return $this->exec();

      }

      /**
       * Deletes data in db
       * @return bool 
       */
      public function delete($table, array $where = []) {
          $sql = "DELETE FROM `$table` ";

          if (!empty($where)) {
              $sql .= " WHERE ";

              foreach ($where as $key => $val) {
                  $sql .= "`$key` = :$key AND ";
              }

              $sql = substr($sql, 0, -4);
          }

          $this->setParam($where);
          $this->setQuery($sql);

          return $this->exec();
      }
  }
