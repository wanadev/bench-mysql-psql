<?php 
require 'vendor/autoload.php';
require __DIR__.'/dbms/Mysql.php';
require __DIR__.'/dbms/Postgresql.php';

use Symfony\Component\Yaml\Yaml;

class Bench
{
    private $iteration;
    private $logs = array();
    private $faker;
    private $psql;
    private $mysql;
    private $pks;

    public function __construct($iteration = 1000)
    {
        $this->iteration = $iteration;
        $this->data = Yaml::parse(file_get_contents('data/data.yml'));
        $this->faker = Faker\Factory::create();

        $this->mysql = new Mysql(false);
        $this->pdomysql = new Mysql(true);
        $this->psql = new Postgresql(false);
        $this->pdopsql = new Postgresql(true);
    }

    public function loadFixtures()
    {
        $this->psql->createDatabase();
        $this->mysql->createDatabase();

        $this->mysql->selectDb();

        $this->psql->loadFixtures();
        $this->mysql->loadFixtures();
    }

    public function loadSql($sql, $name)
    {
        $data = array();
        for ($i=1; $i < $this->iteration+1; $i++) {
            $inc = $this->pks[$name]++;
            
            $d = preg_replace_callback('(\{{(.*?)\}})is', function ($matches) use ($inc) {
                return $this->replaceSql($matches, $inc);
            }, $sql);
            $data[] = $d;
        }

        return $data;
    }

    public function replaceSql($matches, $i)
    {
        $value = $matches[1];
        if (preg_match('/faker_/', $value)) {
            $value = preg_split('/faker_/', $value);
            return addslashes($this->faker->$value[1]);
        } else {
            switch ($value) {
                case 'i':
                    return $i;
                    break;
            }
        }
    }

    public function run()
    {
        $this->psql->connect(true, false);
        $this->pdopsql->connect(true, false);
        
        foreach ($this->data as $name => $data) {
            foreach ($data as $type => $sql) {
                $this->pks[$name] = 1;

                $data = $this->loadSql($sql, $name);
                $this->execute($name, $type, $data, true);

                $data = $this->loadSql($sql, $name);
                $this->execute($name, $type, $data);
            }
        }
    }

    public function execute($name, $type, $data, $is_pdo = false)
    {
        $memory_start = memory_get_usage();
        $time_start = microtime(true);

        foreach ($data as $sql) {
            if ($type === 'psql') {
                $is_pdo ? $this->pdopsql->query($sql) : $this->psql->query($sql);
            } elseif ($type === 'mysql') {
                $is_pdo ? $this->pdomysql->query($sql) : $this->mysql->query($sql);
            }
        }
        
        $time_end = microtime(true);
        $memory_end = memory_get_usage() ;

        $this->logs[$name][$type][$is_pdo]['memory'] = $memory_end - $memory_start;
        $this->logs[$name][$type][$is_pdo]['time'] = $time_end - $time_start;
    }

    public function getMetrics()
    {
        $msg = '';
        foreach ($this->logs as $key => $l) {
            $memory = $key." Memory usage :";
            $time = $key." Execution time :";

            foreach ($l as $k => $db_type) {
                $memory .= ' '.$k.' ('.$db_type[0]['memory'].')';
                $time .= ' '.$k.' ('.round($db_type[0]['time'], 2).' seconds)';
                $memory .= ' pdo_'.$k.' ('.$db_type[1]['memory'].')';
                $time .= ' pdo_'.$k.' ('.round($db_type[1]['time'], 2).' seconds)';
            }

            $msg .= $memory."\n";
            $msg .= $time."\n";
        }

        return $msg;
    }
}
