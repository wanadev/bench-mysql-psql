<?php
require_once 'src/autoload.php';

$bench = new Bench();

$bench->run(array_key_exists(1, $argv) ? $argv[1] : null);
echo $bench->getMetrics();
