Simple PHP Benchmark for Mysql and PostgreSQL
======

Installation
-----------------

For easy use, please run the docker container with docker-compose configuration provided.

Dependencies :
* PostgreSQL 9.x
* Mysql 
* Mysqli extension
* Pgsql
* PDO

Running benchmark
-----------------

First, you must change fixtures database in the path ```fixtures/*.sql```. Now, you can add your SQL queries in the file ```data/data.yml```.

Load fixtures by executing ```load.php``` and run benchmark ```run.php```.