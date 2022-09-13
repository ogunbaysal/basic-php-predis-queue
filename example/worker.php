<?php
require __DIR__ . '/vendor/predis/predis/autoload.php';
Predis\Autoloader::register();

require_once __DIR__ . '/../BasicPhpQueue.php';
require_once __DIR__ . '/tasks.php';

$client = new Predis\Client();
$client->connect();

$queue = new BasicPhpPredisQueue\Queue($client);

$worker = new BasicPhpPredisQueue\Worker($queue);

$worker->run();