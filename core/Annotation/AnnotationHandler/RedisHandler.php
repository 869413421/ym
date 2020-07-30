<?php


namespace Core\Annotation\AnnotationHandler;

use Core\Annotation\Redis;
use Core\BeanFactory;
use Core\Init\DecorationCollection;
use Core\Init\Redis as RedisUtil;
use Swoole\Coroutine\Channel;

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
            saveDataByHash($key, $data, $ttl, $redisAnnotation);
            break;
        case 'sortSet':
            saveDataBySortSet($data, $redisAnnotation);
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

function saveDataByHash(string $key, $value, int $ttl, Redis $annotationRedis)
{
    if (is_object($value))
    {
        $value = json_decode(json_encode($value), true);
    }
    //如果设置了预热，批量插入数据
    $warmup = $annotationRedis->warmup;
    if ($warmup)
    {
        foreach ($value as $item)
        {
            if (!isset($item[$warmup]))
                continue;
            $item_key = $annotationRedis->prefix . $item[$warmup];
            RedisUtil::hMSet($item_key, $item);
            if ($ttl > 0)
            {
                RedisUtil::expire($item_key, $ttl);
            }
        }
    }
    else
    {
        RedisUtil::hMSet($key, $value);
        if ($ttl > 0)
        {
            RedisUtil::expire($key, $ttl);
        }
    }

}

function saveDataBySortSet($value, Redis $annotationRedis)
{


    if ($annotationRedis->coroutine)
    {
        echo '携程取出数据';
        $data = [];
        /** @var $value Channel */
        for ($i = 0; $i < $value->capacity; $i++)
        {
            $channelData = $value->pop(5);
            if (!$channelData)
                continue;
            foreach ($channelData as $item)
            {
                $data[] = $item;
            }
        }
        var_dump($data);
        $value = $data;
    }

    if (is_object($value))
    {
        $value = json_decode(json_encode($value), true);
    }

    foreach ($value as $item)
    {
        $sortKey = $annotationRedis->sortSetKey;
        $sortFiled = $annotationRedis->sortSetFiled;
        $sortIdFiled = $annotationRedis->prefix . $item[$annotationRedis->sortIdFiled];
        RedisUtil::zAdd($sortKey, $item[$sortFiled], $sortIdFiled);
    }
}