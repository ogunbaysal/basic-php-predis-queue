<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/BasicPhpQueue.php';
require_once __DIR__ . '/tasks.php';



$client = new Predis\Client();
$client->connect();

$queue = new SimpleQueue($client);

$worker = new QueueWorker($queue);

$worker->run();