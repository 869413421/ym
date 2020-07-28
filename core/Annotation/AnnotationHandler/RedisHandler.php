<?php


namespace Core\Annotation\AnnotationHandler;

use Core\Annotation\Redis;
use Core\BeanFactory;
use Core\Init\DecorationCollection;

return [
    Redis::class => function (\ReflectionMethod $refMethod, $instance, $self)
    {
        /** @var $decorationCollection DecorationCollection* */
        $decorationCollection = BeanFactory::getBeans(DecorationCollection::class);
        $key = get_class($instance) . '::' . $refMethod->getName();
        $decorationCollection->decorationSet[$key] = function (callable $callBack)
        {
            return function ($params) use ($callBack)
            {
                return call_user_func($callBack, $params);
            };
        };
    }
];