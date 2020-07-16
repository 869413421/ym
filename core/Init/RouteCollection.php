<?php


namespace Core\Init;

use Core\Annotation\Bean;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

/**
 * 路由收集器
 * @Bean()
 */
class RouteCollection
{
    public $routes = [];

    /**
     * 收集路由
     * @param string $url
     * @param array $method
     * @param $handler
     */
    public function addRoute(string $url, array $method, $handler)
    {
        $this->routes[] = [
            'url' => $url,
            'method' => $method,
            'handler' => $handler
        ];
    }

    /**
     * 获取路由分发器
     * @return \FastRoute\Dispatcher
     */
    public function getDispatcher()
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector)
        {
            foreach ($this->routes as $route)
            {
                $routeCollector->addRoute($route['method'], $route['url'], $route['handler']);
            }
        });

        return $dispatcher;
    }
}