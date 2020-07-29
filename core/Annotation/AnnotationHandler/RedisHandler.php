<?php


namespace Core\Annotation\AnnotationHandler;

use Core\Annotation\Redis;
use Core\BeanFactory;
use Core\Init\DecorationCollection;
use Core\Init\Redis as RedisUtil;

return [
    Redis::class => function (\ReflectionMethod $refMethod, $instance, $self)
    {
        /** @var $decorationCollection DecorationCollection* */
        $decorationCollection = BeanFactory::getBeans(DecorationCollection::class);
        $key = get_class($instance) . '::' . $refMethod->getName();
        $decorationCollection->decorationSet[$key] = function (callable $callBack) use ($self)
        {
            /** @var $self Redis */
            return function ($params) use ($callBack, $self)
            {
                $key = $self->prefix . $self->key;
                $data = RedisUtil::get($key);
                if ($data)
                {
                    echo 'Redis 取' . PHP_EOL;
                    return $data;
                }
                else
                {
                    echo 'DB 取' . PHP_EOL;
                    $data = call_user_func($callBack, ...$params);
                    RedisUtil::set($key, json_encode($data));
                    return $data;
                }
            };
        };
    }
];