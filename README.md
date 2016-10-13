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

First, you must change fixtures database in the path ```fixtures/*.sql```.
Next, add your SQL queries in the Yaml file ```data/*.yml```.

Now, run benchmark ```run.php```.