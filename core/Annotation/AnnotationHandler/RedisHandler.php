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

            return function ($params) use ($callBack, $self)
            {
                //如果注解的key中包含#号，截取数组index，使用路由参数作为key
                /** @var $self Redis */
                $findIndex = strpos($self->key, '#');
                if ($findIndex !== false)
                {
                    $keyIndex = (int)substr($self->key, $findIndex + 1);
                    if (!isset($params[$keyIndex]) || !is_string($params[$keyIndex]))
                    {
                        throw new \Exception('Redis Key Null Or Not String');
                    }
                    $key = $self->prefix . $params[$keyIndex];
                }
                else
                {
                    $key = $self->prefix . $self->key;
                }

                $data = getDataToRedis($self, $key);
                if ($data)
                {
                    echo 'Redis 取' . PHP_EOL;
                    return $data;
                }
                else
                {
                    echo 'DB 取' . PHP_EOL;
                    $data = call_user_func($callBack, ...$params);
                    if ($data)
                    {
                        saveDataToRedis($self, $key, $data);
                    }
                    return $data;
                }
            };
        };
    }
];

function getDataToRedis(Redis $redisAnnotation, $key)
{
    switch ($redisAnnotation->type)
    {
        case 'string':
            return RedisUtil::get($key);
            break;
        case 'hash':
            $data = RedisUtil::hGetAll($key);
            if ($data)
            {
                if ($redisAnnotation->incrFiled)
                {
                    RedisUtil::hIncrBy($key, $redisAnnotation->incrFiled, $redisAnnotation->incrValue);

                }
            }
            return RedisUtil::hGetAll($key);
            break;
        default:
            return null;
    }
}

function saveDataToRedis(Redis $redisAnnotation, $key, $data)
{
    $ttl = $redisAnnotation->ttl;
    switch ($redisAnnotation->type)
    {
        case 'string':
            saveDataByString($key, json_encode($data), $ttl);
            break;
        case 'hash':
            saveDataByHash($key, $data, $ttl, $redisAnnotation->incrFiled, $redisAnnotation->incrValue);
            break;
    }
}

function saveDataByString($key, $value, $ttl)
{
    if ($ttl > 0)
    {
        RedisUtil::setex($key, $ttl, $value);
    }
    else
    {
        RedisUtil::set($key, $value);
    }
}

function saveDataByHash(string $key, $value, int $ttl, string $incrFiled = '', int $incrValue = 0)
{
    if (is_object($value))
    {
        $value = json_decode(json_encode($value), true);
    }
    RedisUtil::hMSet($key, $value);
    if ($ttl > 0)
    {
        RedisUtil::expire($key, $ttl);
    }
}