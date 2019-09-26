<?php declare(strict_types=1);

namespace SilerExt\Http;

use Siler\Container;
use Siler\Http\Response;
use function SilerExt\Config\{config};

function enableCors(string $origin = '*') {
    Response\header('Access-Control-Allow-Origin', $origin);
    Response\header('Access-Control-Allow-Headers', 'content-type');
}

function finishRequest() {
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    runBgTasks();
}

/**
 * SilerExt\Http\addBgTask(function() use ($user) {
 *     sleep(5);
 *     file_put_contents(Helper\config('storage_path') . '/test.'. microtime(true) .'.tmp', json_encode("ok", JSON_NUMERIC_CHECK));
 * });
 */
function addBgTask(callable $closure) {
    if (!Container\has("bg_task_queue")) {
        Container\set("bg_task_queue", new TaskQueue());
    }

    $taskQueue = Container\get("bg_task_queue");
    $taskQueue->addTask($closure);
}

function runBgTasks() {
    if (Container\has("bg_task_queue")) {
        Container\get("bg_task_queue")->runTasks();
    }
}

final class TaskQueue
{
    private $tasks = [];

    public function addTask(callable $closure)
    {
        $this->tasks[] = $closure;
    }

    public function runTasks()
    {
        foreach ($this->tasks as $task) {
            $task();
        }
    }
}
