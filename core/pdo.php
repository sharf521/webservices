<?php

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

class Db
{
    /**
     * 实例数组
     * @var array
     */
    protected static $instance = array();

    /**
     * 获取实例
     * @param string $config_name
     * @throws \Exception
     */
    public static function instance($config_name)
    {
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

    /**
     * 关闭数据库实例
     * @param string $config_name
     */
    public static function close($config_name)
    {
        if (isset(self::$instance[$config_name])) {
            self::$instance[$config_name]->closeConnection();
            self::$instance[$config_name] = null;
        }
    }

    /**
     * 关闭所有数据库实例
     */
    public static function closeAll()
    {
        foreach (self::$instance as $connection) {
            $connection->closeConnection();
        }
        self::$instance = array();
    }
}

class DbConnection
{
    protected $pdo;
    protected $dbfix;

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

    /**
     * 创建pdo实例
     */
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

    /*
    *   关闭连接
    */
    public function closeConnection()
    {
        $this->pdo = null;
    }

    function query($sql)
    {
        $result = $this->pdo->exec($sql);
        return $result;
    }

    private function select($sql)
    {
        $rs = $this->pdo->query($sql);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        return $rs;
    }

    public function get_one($sql)
    {
        $rs = $this->select($sql);
        $result = $rs->fetch();
        return $result;
    }

    public function get_all($sql)
    {
        $rs = $this->select($sql);
        $result = $rs->fetchAll();
        return $result;
    }

    /**
     * 开始事务
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * 事务回滚
     */
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