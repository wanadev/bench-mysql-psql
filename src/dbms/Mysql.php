<?php 
use Symfony\Component\Yaml\Yaml;

class Mysql
{
    private $config;
    private $mysqli;
    private $pdomysql;
    private $is_pdo;

    public function __construct($is_pdo = false)
    {
    	$this->is_pdo = $is_pdo;
        $config = Yaml::parse(file_get_contents('config.yml'));
        $this->config = $config['mysql'];
        $this->connect($is_pdo);
    }

    public function connect($is_pdo = false)
    {
        if ($this->is_pdo) {
            $dsn = "mysql:dbname=".$this->config['dbname'].";host=".$this->config['host'];
            $this->mysql = new PDO($dsn, $this->config['username'], $this->config['password']);
        } else {
            $this->mysqli = new  mysqli($this->config['host'], $this->config['username'], $this->config['password'], $this->config['dbname'], $this->config['port']);
            $this->mysqli->select_db($this->config['dbname']) or die('Error selecting MySQL database');
        }
    }

    public function createDatabase()
    {
        $this->query("DROP DATABASE ".$this->config['dbname']);
        $this->query("CREATE DATABASE ".$this->config['dbname']);
    }

    public function close()
    {
    	if ($this->is_pdo) {
    		$this->mysql = null;
    	}
    	else {
        	$this->mysqli->close();
        	$this->mysqli = null;
        }
    }

    public function query($sql)
    {
        if ($this->is_pdo) {
            $this->mysql->query($sql) or die('PDOMYSQL: '.$sql.' | ERROR: '.print_r($this->mysql->errorInfo(), true));
        } else {
            $this->mysqli->query($sql) or die('MYSQL: '.$sql.' | ERROR: '.$this->mysqli->error);
        }
    }

    public function selectDb()
    {
    	if ($this->is_pdo) {
    		$this->connect(true);
    	}
    	else {
    		$this->mysqli->select_db($this->config['dbname']) or die('Error selecting MySQL database: ' . mysql_error());
    	}
    }

    public function loadFixtures()
    {
        $lines = file('fixtures/mysql.sql');
        $templine = '';
        foreach ($lines as $l) {
            $templine .= $l;

            if (substr(trim($l), -1, 1) == ';') {
                $this->query($templine);
                $templine = '';
            }
        }
    }
}
