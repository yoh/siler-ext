<?php declare(strict_types=1);

namespace SilerExt\Http;

use Siler\Container;
use Siler\Http\Response;
use function SilerExt\Config\{config};
use function Siler\Http\{session as silerSession};

function enableCors(string $origin = '*')
{
    Response\header('Access-Control-Allow-Origin', $origin);
    Response\header('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS');
    Response\header('Access-Control-Allow-Headers', 'content-type');
    Response\header('Access-Control-Allow-Credentials', 'true');
}

function finishRequest()
{
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    runBgTasks();
}

function session(?string $key = null, $default = null, bool $autoClose = true)
{
    if (!Container\has("session") && !Container\get("session")) {
        Container\set("session", session_start());
        if ($autoClose) {
            session_write_close();
        }
    }

    return silerSession($key, $default);
}

/**
 * SilerExt\Http\addBgTask(function() use ($user) {
 *     sleep(5);
 *     file_put_contents(Helper\config('storage_path') . '/test.'. microtime(true) .'.tmp', json_encode("ok", JSON_NUMERIC_CHECK));
 * });
 */
function addBgTask(callable $closure)
{
    if (!Container\has("bg_task_queue")) {
        Container\set("bg_task_queue", new TaskQueue());
    }

    $taskQueue = Container\get("bg_task_queue");
    $taskQueue->addTask($closure);
}

function runBgTasks()
{
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
