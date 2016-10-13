<?php 
use Symfony\Component\Yaml\Yaml;

class Postgresql
{
    private $config;
    private $psql;
    private $is_pdo;


    public function __construct($is_pdo = false)
    {
    	$this->is_pdo = $is_pdo;
        $config = Yaml::parse(file_get_contents('config.yml'));
        $this->config = $config['psql'];
        $this->connect(true);
    }

    public function connect($fixture_mode = false)
    {
        if ($this->is_pdo) {
            $dbname = $fixture_mode ? "" : " dbname=".$this->config['dbname'];
            $dsn = "pgsql:".$dbname.";host=".$this->config['host'];
            $this->pgsql = new PDO($dsn, $this->config['username'], $this->config['password']);
        } else {
            $dbname = $fixture_mode ? "" : " dbname=".$this->config['dbname'];
            $this->psql = pg_connect("host=".$this->config['host']." port=".$this->config['port']." user=".$this->config['username']." password=".$this->config['password'].$dbname) or die('Error connecting to Postgres server: ' . pg_last_error());
        }
    }

    public function createDatabase()
    {
        $this->query("DROP DATABASE IF EXISTS ".$this->config['dbname']);
        $this->query("CREATE DATABASE ".$this->config['dbname']);

        if ($this->psql !== null) {
            pg_close($this->psql);
        }

        $this->connect();
    }

    public function close()
    {
    	if ($this->is_pdo) {
    		$this->pgsql = null;
    	}
    	else {
        	pg_close($this->psql);
        }
    }

    public function getConnexion()
    {
        return $this->psql;
    }

    public function query($sql)
    {
        if ($this->is_pdo) {
            $this->pgsql->query($sql) or die('PDOPSQL: '.$sql.' | ERROR: '.print_r($this->pgsql->errorInfo(), true));
        } else {
            pg_query($this->psql, $sql) or die('PSQL: '.$sql.' | ERROR: '.pg_last_error($this->psql));
        }
    }

    public function loadFixtures()
    {
        $lines = file('fixtures/psql.sql');
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
