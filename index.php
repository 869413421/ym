<?php
require __DIR__ . '/vendor/autoload.php';

use Core\BeanFactory;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/define.php';
BeanFactory::init();
$routeCollection = BeanFactory::getBeans('RouteCollection');
$dispatcher = $routeCollection->getDispatcher();

$http = new Server('0.0.0.0', 39008);
$http->on('request', function (Request $request, Response $response) use ($dispatcher)
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
        $routeInfo = $dispatcher->dispatch($method, $uri);

        switch ($routeInfo[0])
        {
            case FastRoute\Dispatcher::NOT_FOUND:
                $response->status(404);
                $response->end('NOT FOUND HANDSOME BOY');
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $response->status(404);
                $response->end($allowedMethods);
                break;
            case FastRoute\Dispatcher::FOUND: // 找到对应的方法
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
        $response->end($exception->getMessage());
    }
});

$http->start();
