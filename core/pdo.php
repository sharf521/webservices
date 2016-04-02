<?php

class DbConnection
{
    protected $pdo;
    protected $dbfix;
    protected $sQuery;

    public function __construct($host, $port, $user, $password, $db_name, $charset = 'utf8', $dbfix = '')
    {
        $this->settings = array(
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'password' => $password,
            'dbname' => $db_name,
            'charset' => $charset
        );
        $this->dbfix = $dbfix;
        $this->connect();
    }

    //创建pdo实例
    protected function connect()
    {
        try {
            $dsn = 'mysql:dbname=' . $this->settings["dbname"] . ';host=' . $this->settings["host"] . ';port=' . $this->settings['port'];
            $this->pdo = new \PDO($dsn, $this->settings["user"], $this->settings["password"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . (!empty($this->settings['charset']) ? $this->settings['charset'] : 'utf8')));
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            //$this->error_msg($e->getMessage());
            die('数据库连接失败！');
        }
    }

    //关闭连接
    public function closeConnection()
    {
        $this->pdo = null;
    }

    function query($query, $params = array())
    {
        $this->sQuery = $this->pdo->prepare($query);
        if (!empty($params) && is_array($params)) {
            foreach ($params as $k => &$v) {
                if (is_string($k)) {
                    $this->sQuery->bindParam(':' . $k, $v);
                } else {
                    $this->sQuery->bindParam($k + 1, $v);
                }
            }
        }
        $this->sQuery->execute();
        $rawStatement = explode(" ", trim($query));
        $statement = strtolower($rawStatement[0]);
        if ($statement === 'select' || $statement === 'show') {
            $this->sQuery->setFetchMode(\PDO::FETCH_ASSOC);
        } elseif ($statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } elseif ($statement === 'insert') {
            if ($this->sQuery->rowCount() > 0) {
                return $this->pdo->lastInsertId();
            }
        }
    }

    public function get_one($sql, $param = array())
    {
        $this->query($sql, $param);
        $result = $this->sQuery->fetch();
        return $result;
    }

    public function get_all($sql, $param = array())
    {
        $this->query($sql, $param);
        $result = $this->sQuery->fetchAll();
        return $result;
    }

    //开始事务
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    //提交事务
    public function commit()
    {
        $this->pdo->commit();
    }

    //事务回滚
    public function rollBack()
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function insert($table, $dataArray)
    {
        $field = $value = '';
        foreach ($dataArray as $key => $val) {
            $field .= "`$key`,";
            $value .= "'$val',";
        }
        $field = substr($field, 0, -1);
        $value = substr($value, 0, -1);
        $sql = "INSERT INTO " . $this->dbfix . $table . " ($field) VALUES ($value)";
        return $this->query($sql);
    }

    public function update($talbe, $dataArray, $where)
    {
        $_sql = array();
        foreach ($dataArray as $key => $value) {
            $_sql[] = "`$key`='$value'";
        }
        $value = implode(',', $_sql);
        $sql = "UPDATE " . $this->dbfix . $talbe . " SET $value WHERE $where";
        return $this->query($sql);
    }

    public function delete($table, $where)
    {
        if (is_numeric($where)) {
            $str = "id=$where limit 1";
        } elseif (is_array($where)) {
            $str = ' 1=1 ';
            foreach ($where as $k => $v) {
                $str .= " and `$k`='$v'";
            }
        } else {
            $str = $where;
        }
        $sql = "delete from {$this->dbfix}$table where $str";
        return $this->query($sql);
    }

    public function one($table, $array = array())
    {
        $str = ' where 1=1';
        foreach ($array as $k => $v) {
            $str .= " and `$k`='$v'";
        }
        $sql = 'select * from ' . $this->dbfix . $table . $str . ' limit 1';
        //echo $sql;
        return $this->get_one($sql);
    }

    public function insert_id()
    {
        return $this->pdo->lastInsertId();
    }

    function error_msg($msg)
    {
        $mysql_dir = 'data';
        $dtime = date("Y-m-d", time());
        $ip = $this->ip();
        $file = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
        if (!file_exists($mysql_dir . "/mysql_error")) {
            mkdir($mysql_dir . "/mysql_error", 0777);
        }
        $fp = @fopen($mysql_dir . "/mysql_error/" . $dtime . ".log", "a+");
        $time = date("H:i:s");
        //debug_print_backtrace();
        $str = "{time:$time}\t{ip:" . $ip . "}\t{error:" . $msg . "}\t{file:" . $file . "}\t\r\n";
        @fputs($fp, $str);
        @fclose($fp);
        echo $str;
        return false;
    }

    function ip()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_address = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_address = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip_address = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip_address = '';
        }
        return $ip_address;
    }

    //禁止克隆
    final public function __clone()
    {
    }

    //析构函数-资源回收
    function __destruct()
    {
        $this->closeConnection();
    }
    //////////////////////////////////////
    protected function reset()
    {
        $this->distinct = '';
        $this->select = '';
        $this->from = '';
        $this->where = '';
        $this->groupBy = '';
        $this->having = '';
        $this->orderBy = '';
        $this->limit = '';
    }
    private function buildSelect()
    {
        if (empty($this->select)) {
            $this->select = '*';
        }
        $sql = "select {$this->distinct} {$this->select} from {$this->from}"
            . $this->where
            . $this->orderBy
            . $this->groupBy
            . $this->limit;
        $this->reset();
        return $sql;
    }
    public function table($table)
    {
        $this->from = $table;
        return $this;
    }

    public function select($str)
    {
        $this->select = $str;
        return $this;
    }

    public function distinct()
    {
        $this->distinct = 'distinct';
        return $this;
    }

    public function where($where)
    {
        $this->where = ' where ' . $where;
        return $this;
    }

    public function orderBy($str)
    {
        $this->orderBy = ' order by ' . $str;
        return $this;
    }

    public function groupBy($str)
    {
        $this->groupBy = ' group by ' . $str;
        return $this;
    }

    public function having($str)
    {
        $this->having = ' having ' . $str;
        return $this;
    }

    public function limit($str)
    {
        $this->limit = ' limit ' . $str;
        return $this;
    }

    //取一行
    public function row()
    {
        $sql = $this->buildSelect() . " limit 1";
        return $this->get_one($sql);
    }

    //取一行中一列的值
    public function value($col)
    {
        $this->select = $col;
        $row = $this->row();
        return $row[$col];
    }

    //取多行
    public function all()
    {
        $sql = $this->buildSelect();
        return $this->get_all($sql);
    }

    //取一列
    public function lists($col, $key = null)
    {
        $this->select = $col;
        if ($key !== null) {
            $this->select .= ',' . $key;
        }
        $sql = $this->buildSelect();
        $result = $this->get_all($sql);
        $arr = array();
        foreach ($result as $k => $v) {
            if ($key == null) {
                $arr[$k] = $v[$col];
            } else {
                $arr[$v[$key]] = $v[$col];
            }
        }
        return $arr;
    }
}

class Config
{
    // 数据库实例1
    public static $db1 = array(
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => 'root',
        'dbname' => 'user_center',
        'charset' => 'utf8',
        'dbfix' => 'plf_'
    );
}

class DB
{
    //实例数组
    protected static $instance = array();

   //获取实例
    /**
     * @param null $config_name
     * @return mixed
     * @throws Exception
     */
    public static function instance($config_name = null)
    {
        if ($config_name == null) {
            $config_name = 'db1';
        }
        if (!isset(Config::$$config_name)) {
            echo "Config::$config_name not set\n";
            throw new \Exception("Config::$config_name not set\n");
        }

        if (empty(self::$instance[$config_name])) {
            $config = Config::$$config_name;
            self::$instance[$config_name] = new DbConnection($config['host'], $config['port'], $config['user'], $config['password'], $config['dbname'], $config['charset'], $config['dbfix']);
        }
        return self::$instance[$config_name];
    }

/////////////////////////////////////////////
    public static function table($table, $connection = null)
    {
        //return static::instance($connection)->from($table);
        return self::instance($connection)->table($table);
    }

    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([self::instance(), $method], $parameters);
    }
///////////////////////////////////////
    //关闭数据库实例
    public static function close($config_name)
    {
        if (isset(self::$instance[$config_name])) {
            self::$instance[$config_name]->closeConnection();
            self::$instance[$config_name] = null;
        }
    }

    //关闭所有数据库实例
    public static function closeAll()
    {
        foreach (self::$instance as $connection) {
            $connection->closeConnection();
        }
        self::$instance = array();
    }
}

/*
$mysql = Db::instance('db1');
$a = $mysql->get_one("select * from test11 limit 1");
print_r($a);
$a = $mysql->get_all("select * from test");
print_r($a);


try {

    $mysql->beginTransaction();
    echo $mysql->insert('test', array('name' => 1, 'value' => 2));
    echo $mysql->insert('test', array('name' => 1, 'value' => 2));
    echo $mysql->insert_id();

    $mysql->commit();

} catch (Exception $e) {
    $mysql->rollBack();
    echo "Failed: " . $e->getMessage();
}
*/