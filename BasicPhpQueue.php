<?php
namespace BasicPhpPredisQueue;

abstract class Task {
    public abstract function action();
    public abstract function fromArray(array $array);
    public abstract function toArray() : array;
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
        $data = $task->toArray();
        $data['class'] = get_class($task);
        $data = json_encode($data);
        $this->client->rpush($this->queue_name, [$data]);
    }
    public function dequeue() : ?array {
        $json = $this->client->lpop($this->queue_name);
        if ($json === null) {
            return null;
        }
        return json_decode($json, true);
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
        $data = $this->queue->dequeue();
        while ($data) {
            $class = $data['class'];
            $this->log(self::INFO, 'Task is found. It is parsing...', $class);
            if (class_exists($class) === false) {
                $this->log(self::WARNING, 'Task class is not found.', $class);
                $data = $this->queue->dequeue();
                continue;
            }
            $task = new $class();
            $task->fromArray($data);
            $this->log(self::INFO, 'Task is parsed. It is running...', $class);
            $task->action();
            $this->log(self::INFO, 'Task is finished.', $class);
            $data = $this->queue->dequeue();
        }
    }

    public function log($type, $message, $taskName = null) {
        $date = date('Y-m-d H:i:s');
        $taskName = $taskName ?? 'QueueWorker';
        echo "[$date] [$type] [$taskName] $message" . PHP_EOL;
    }
}