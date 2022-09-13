<?php
namespace BasicPhpPredisQueue;

abstract class Task {
    public abstract function action();
    public abstract function fromArray(array $array);
}

class Queue {
    private \Predis\Client $client;
    private string $queue_name = 'queue';
    public function __construct(\Predis\Client $client, ?string $queue_name = null) {
        $this->client = $client;
        if ($queue_name !== null) {
            $this->queue_name = $queue_name;
        }
    }
    public function enqueue(Task $task) {
        $this->client->rpush($this->queue_name, [serialize($task)]);
    }
    public function dequeue() : ?Task {
        $data = $this->client->lpop($this->queue_name);
        if ($data === null) {
            return null;
        }
        try {
            return unserialize($data);
        }
        catch (\Exception $exception) {
            return null;
        }
    }
    public function getLength() : int {
        return $this->client->llen($this->queue_name);
    }
}

class Worker {
    private const INFO = 'INFO';
    private const WARNING = 'WARNING';
    private Queue $queue;
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
        $this->log(self::INFO, 'Queue is initialized.');
    }
    public function run() {
        $this->log(self::INFO, 'Queue is running.');
        $task = $this->queue->dequeue();
        while ($task) {
            if (($task instanceof Task) === false) {
                $this->log(self::WARNING, 'Queue Class is not an instance of Task.');
                $task = $this->queue->dequeue();
                continue;
            }
            $class = get_class($task);
            $this->log(self::INFO, 'Task is found. It is running...', $class);
            $task->action();
            $this->log(self::INFO, 'Task is finished.', $class);
            $task = $this->queue->dequeue();
        }
    }

    public function log($type, $message, $taskName = null) {
        $date = date('Y-m-d H:i:s');
        $taskName = $taskName ?? 'QueueWorker';
        echo "[$date] [$type] [$taskName] $message" . PHP_EOL;
    }
}