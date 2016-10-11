<?php
require_once 'src/autoload.php';

$bench = new Bench(1000, true);
$bench->loadFixtures();
$bench->run();

echo $bench->getMetrics();
