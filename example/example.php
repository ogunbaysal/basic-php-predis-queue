<?php
require __DIR__ . '/vendor/predis/predis/autoload.php';
Predis\Autoloader::register();

require_once __DIR__ . '/../BasicPhpQueue.php';
require_once __DIR__ . '/tasks.php';

$client = new Predis\Client();
$client->connect();

$queue = new BasicPhpPredisQueue\Queue($client);

$task = new MailerTask();
$task->fromArray([
    'template' => 'welcome',
    'email' => 'ogunbaysaltr@gmail.com',
    'subject' => 'Welcome to our site',
    'data' => [
        'name' => 'Ogun',
        'surname' => 'Baysal',
    ],
]);

$queue->enqueue($task);