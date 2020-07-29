<?php


namespace Core\Annotation\AnnotationHandler;

use Core\Annotation\RequestMapping;
use Core\BeanFactory;
use Core\Init\DecorationCollection;

return [
    RequestMapping::class => function (\ReflectionMethod $method, $instance, $annotationSelf)
    {
        //获取到路由收集器
        $routeCollect = BeanFactory::getBeans('RouteCollection');
        $url = $annotationSelf->url;
        $requestMethod = count($annotationSelf->method) > 0 ? $annotationSelf->method : ['GET'];

        //将路由塞到路由收集器当中
        $routeCollect->addRoute($url, $requestMethod, function ($params, $extParams) use ($method, $instance)
        {
            $inputParams = [];
            //检查是否传递了方法参数，映射到方法当中
            foreach ($method->getParameters() as $parameter)
            {
                if (isset($params[$parameter->getName()]))
                {
                    $inputParams[] = $params[$parameter->getName()];
                }
                else
                {
                    $controllerParam = false;
                    foreach ($extParams as $extParam)
                    {
                        //判断当前拓展参数是否属于方法类型的对象
                        if ($parameter->getClass()->isInstance($extParam))
                        {
                            $controllerParam = $extParam;
                            break;
                        }
                    }
                    $inputParams[] = $controllerParam;
                }
            }

            //通过装饰器对方法进行调用
            /** @var $decorationCollection DecorationCollection* */
            $decorationCollection = BeanFactory::getBeans(DecorationCollection::class);
            return $decorationCollection->exec($method, $instance, $inputParams);
        });

        return $instance;
    },
];