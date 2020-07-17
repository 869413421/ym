<?php

namespace Core\Server;


use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class HttpServer
{
    private $server;

    public function __construct()
    {
        $this->server = new Server('0.0.0.0', 39002);

        $this->server->on('start', [$this, "onStart"]);
        $this->server->on('workerstart', [$this, "onWorkStart"]);
        $this->server->on('request', [$this, "onRequest"]);
        $this->server->on('shutdown', [$this, "onShutdown"]);
    }

    public function onStart(Server $server)
    {
        $pid = $server->master_pid;
        file_put_contents(ROOT_PATH . '/ym.pid', $pid);
    }

    public function onWorkStart(Server $server, $wordId)
    {

    }

    public function onRequest(Request $request, Response $response)
    {

    }

    public function onShutdown(Server $server)
    {
        unlink(ROOT_PATH . '/ym.pid');
    }

    public function run()
    {
        $this->server->start();
    }
}