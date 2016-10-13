<?php
require_once 'src/autoload.php';

$bench = new Bench(1000);
$bench->run();

echo $bench->getMetrics();
