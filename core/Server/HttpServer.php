<?php

namespace Core\Server;


use Core\BeanFactory;
use Core\Process\MonitorProcess;
use Exception;
use FastRoute\Dispatcher;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class HttpServer
{
    private $server;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct()
    {
        $this->server = new Server('0.0.0.0', 39008);

        $this->server->on('Start', [$this, "onStart"]);
        $this->server->on('WorkerStart', [$this, "onWorkStart"]);
        $this->server->on('ManagerStart', [$this, "onManagerStart"]);
        $this->server->on('Request', [$this, "onRequest"]);
        $this->server->on('ShutDown', [$this, "onShutdown"]);
    }

    public function onStart(Server $server)
    {
        $pid = $server->master_pid;
        file_put_contents(ROOT_PATH . '/ym.pid', $pid);
        cli_set_process_title('YM MasterProcess');
    }

    public function onManagerStart(Server $server)
    {
        cli_set_process_title('YM ManagerProcess');
    }

    public function onWorkStart(Server $server, $wordId)
    {
        cli_set_process_title('YM WorkProcess');
        //自动装载初始化
        BeanFactory::init();
        //获取路由收集器
        $routeCollection = BeanFactory::getBeans('RouteCollection');
        $this->dispatcher = $routeCollection->getDispatcher();
    }

    public function onRequest(Request $request, Response $response)
    {
        try
        {
            $nowRequest = \Core\Http\Request::getInstance($request);
            $nowResponse = \Core\Http\Response::init($response);
            //请求的方法
            $method = $nowRequest->getMethod();
            //请求的uri
            $uri = $nowRequest->getUri();
            // 去除查询字符串( ? 后面的内容) 和 解码 URI
            if (false !== $pos = strpos($uri, '?'))
            {
                $uri = substr($uri, 0, $pos);
            }
            $uri = rawurldecode($uri);
            $routeInfo = $this->dispatcher->dispatch($method, $uri);
            switch ($routeInfo[0])
            {
                case Dispatcher::NOT_FOUND:
                    $response->status(404);
                    $response->end('NOT FOUND HANDSOME BOY');
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    $response->status(404);
                    $response->end($allowedMethods);
                    break;
                case Dispatcher::FOUND: // 找到对应的方法
                    $handler = $routeInfo[1]; // 获得处理函数
                    $vars = $routeInfo[2]; // 获取请求参数
                    $extVars = [
                        $nowRequest,
                        $nowResponse
                    ];

                    $nowResponse->setBody($handler($vars, $extVars));
                    $nowResponse->end();
                    break;
            }
        }
        catch (Exception $exception)
        {
            var_dump($exception->getFile(), $exception->getLine(), $exception->getTraceAsString());
            $response->end($exception->getMessage());
        }
    }

    public function onShutdown(Server $server)
    {
        unlink(ROOT_PATH . '/ym.pid');
    }

    public function run()
    {
        $this->server->addProcess((new MonitorProcess())->run());
        $this->server->start();
    }
}