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
    private $data = array();
    private $is_pdo;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    public function init()
    {
        $path = './data';
        $files = array_diff(scandir($path), array('.', '..'));
        $this->data = array();

        foreach ($files as $file) {
            if(substr($file, -3) == 'yml') {
                $this->data = $this->data + Yaml::parse(file_get_contents($path.'/'.$file));
            }
        }

        $this->mysql = new Mysql($this->is_pdo);
        $this->psql = new Postgresql($this->is_pdo);
    }

    public function run($iteration = 1000)
    {
        echo "RUNNING ".$iteration." iterations \n\n";

        $this->iteration = $iteration;

        $this->is_pdo = true;
        $this->runner();
        
        $this->is_pdo = false;
        $this->runner();
    }

    private function runner()
    {
        $this->init();
        $this->loadFixtures();
        foreach ($this->data as $name => $data) {
            foreach ($data as $type => $sql) {
                $this->pks[$name] = 1;

                $data = $this->loadSql($sql, $name);

                $this->execute($name, $type, $data);
            }
        }
        
        $this->close();
    }

    public function loadFixtures()
    {
        $this->psql->createDatabase();
        $this->mysql->createDatabase();

        $this->mysql->selectDb();

        $this->psql->loadFixtures();
        $this->mysql->loadFixtures();
        $this->psql->connect(false);
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
            $value = preg_replace('/faker_/','', $value);
            return addslashes($this->faker->$value);
        }
        else if (preg_match('/related_/', $value)) {
            $value = preg_replace('/related_/','', $value);
            $id = $this->pks[$value];
            return $id-$i;
        }
        else {
            switch ($value) {
                case 'i':
                    return $i;
                    break;
                case 'rand':
                    return rand(0,$this->iteration);
                    break;
            }
        }
    }

    private function close()
    {
        $this->psql->close($sql);
        $this->mysql->close($sql);
    }

    private function execute($name, $type, $data)
    {
        $time_start = microtime(true);
        foreach ($data as $sql) {
            if ($type === 'psql') {
                $this->psql->query($sql);
            } elseif ($type === 'mysql') {
                $this->mysql->query($sql);
            }
        }
        
        $time_end = microtime(true);

        $type = $this->is_pdo ? 'pdo_'.$type : $type;
        $this->logs[$name][$type]['time'] = $time_end - $time_start;
    }

    public function getMetrics()
    {
        $msg = '';
        foreach ($this->logs as $key => $l) {
            $time = strtoupper($key)."\n";

            foreach ($l as $k => $db_type) {
                $time .= $k." in ".round($db_type['time'], 2)." seconds \n";
            }

            $msg .= $time."\n";
        }

        return $msg;
    }
}
