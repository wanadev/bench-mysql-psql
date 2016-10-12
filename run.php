<?php
require_once 'src/autoload.php';

$bench = new Bench(2000);
$bench->loadFixtures();
$bench->run();

echo $bench->getMetrics();
