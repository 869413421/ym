<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/define.php';

use Core\Server\HttpServer;
use Swoole\Process;

if (count($argv) < 2)
{
    echo 'pleas input command';
    exit();
}

$cmd = $argv[1];

if ($cmd == 'start')
{
    $httpServer = new HttpServer();
    $httpServer->run();
}

if ($cmd == 'stop')
{
    $pid = (int)file_get_contents(ROOT_PATH . '/ym.pid');
    if ($pid && $pid !== 0)
    {
        Process::kill($pid);
        exit();
    }
    else
    {
        echo 'process not found ';
        exit();
    }
}

echo 'Unknown command';
exit();