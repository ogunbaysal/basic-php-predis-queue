# BasicPhpPredisQueue

This library aims to provide a simple queue implementation using Redis and PHP Predis Library.

## Getting Started

There are two main things you need to do to get started:
- Install the [predis/predis](https://github.com/predis/predis) library.
- Define Task classes that implement the `BasicPhpPredisQueue\Task` interface.

### Usage

Firstly, you need to create a task class that implements the `BasicPhpPredisQueue\Task` interface. This interface has a method: `action()`. The `action()` method is where you put the code that you want to run when the task is executed.
In order to save a task to predis, you need to implement two other methods: `toArray()` and `fromArray(array $data)`. These methods are used to convert the task to an array and back again. This is so that the task can be saved to Redis.

```php

class MailerTask extends BasicPhpPredisQueue\Task {
    private string $template = '';
    private string $email = '';
    private string $subject = '';
    private array $data = [];

    public function action()
    {
        // Send email using data.
        var_dump([
            'template' => $this->template,
            'email' => $this->email,
            'subject' => $this->subject,
            'data' => $this->data,
        ]);
    }

    public function fromArray(array $array)
    {
        $this->template = $array['template'];
        $this->email = $array['email'];
        $this->subject = $array['subject'];
        $this->data = $array['data'];
    }

    public function toArray(): array
    {
        return [
            'template' => $this->template,
            'email' => $this->email,
            'subject' => $this->subject,
            'data' => $this->data,
        ];
    }
}
```

Second step is to create a queue instance and add the task to the queue.

```php
<?php

require __DIR__ . '/vendor/predis/predis/autoload.php';
Predis\Autoloader::register();

require_once __DIR__ . '/../BasicPhpQueue.php';
require_once __DIR__ . '/tasks.php';

$client = new Predis\Client();
$client->connect();

$queue = new SimpleQueue($client);

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
```

### Setup Worker

You can use worker to run the tasks in the queue. You can run the worker as a daemon or as a cron job.

```php
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
```

You can add worker to your crontab file to run it periodically.

```bash
* * * * * php /path/to/worker.php
```

## Authors

- **Ogün Baysal**
