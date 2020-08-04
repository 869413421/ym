<?php

use Core\Annotation\RedisLock;
use Core\BeanFactory;
use Core\Init\DecorationCollection;
use Core\Init\Redis;

return [
    RedisLock::class => function (\ReflectionMethod $refMethod, $instance, $self)
    {
        /** @var $decorationCollection DecorationCollection* */
        $decorationCollection = BeanFactory::getBeans(DecorationCollection::class);
        $key = get_class($instance) . '::' . $refMethod->getName();
        $decorationCollection->decorationSet[$key] = function (callable $callBack) use ($self)
        {

            return function ($params) use ($callBack, $self)
            {
                try
                {
                    if (lock($self, $params))
                    {
                        return call_user_func($callBack, ...$params);
                    }
                    else
                    {
                        return false;
                    }
                }
                catch (Exception $exception)
                {
                    throw $exception;
                }
                finally
                {
                    deleteLock($self, $params);
                }


            };
        };
    }
];
function getKey(RedisLock $annotationRedisLock, $params)
{
    $findIndex = strpos($annotationRedisLock->key, '#');
    if ($findIndex !== false)
    {
        $keyIndex = (int)substr($annotationRedisLock->key, $findIndex + 1);
        if (!isset($params[$keyIndex]) || !is_string($params[$keyIndex]))
        {
            throw new \Exception('Redis Key Null Or Not String');
        }
        return $annotationRedisLock->prefix . $params[$keyIndex];
    }
    else
    {
        throw new \Exception('Redis Key Null');
    }
}

//获取锁
function getLock(RedisLock $annotationRedisLock, $params)
{
    $luaScript = <<<script
        local key = KEYS[1];
        local param = KEYS[2];
        local expire = ARGV[1];
        if redis.call('setnx',key,param) == 1 then
            return redis.call('expire',key,expire);
            end
        return 0
script;
    $key = getKey($annotationRedisLock, $params);
    $value = time();
    return Redis::eval($luaScript, [$key, $value, $annotationRedisLock->expire], 2);
}

//删除锁
function deleteLock(RedisLock $annotationRedisLock, $params)
{
    $luaScript = <<<script
        local key = KEYS[1];
        return redis.call('del',key);
script;
    $key = getKey($annotationRedisLock, $params);
    return Redis::eval($luaScript, [$key], 1);
}

//是否锁定成功
function lock(RedisLock $annotationRedisLock, $params)
{
    while ($annotationRedisLock->retry > 0)
    {
        $lock = getLock($annotationRedisLock, $params);
        if ($lock)
        {
            return true;
        }
        usleep(1000 * 100 * 1);
    }

    return false;
}

