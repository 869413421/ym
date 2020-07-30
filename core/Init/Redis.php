<?php

namespace Core\Init;


use Core\Annotation\Bean;
use Core\BeanFactory;

/**
 * @Bean()
 * @method static bool set(string $key, string $value)
 * @method static string get(string $key)
 * @method static string setex(string $key, int $ttl, string $value)
 * @method static bool hMSet(string $key, $value)
 * @method static bool expire(string $key, int $ttl)
 * @method static array hGetAll(string $key)
 * @method static bool hIncrBy(string $key, string $hashKey, int $value)
 * @method static bool zAdd(string $key, int $score1, $value1)
 */
class Redis
{
    public static function __callStatic($name, $arguments)
    {
        /** @var $pool RedisPool */
        $pool = BeanFactory::getBeans(RedisPool::class);
        $connectionInstance = $pool->getConnectionInstance();
        try
        {
            if (!$connectionInstance)
            {
                throw new \RedisException('connection empty');
            }

            $redis = $connectionInstance->instance;
            return $redis->$name(...$arguments);
        }
        catch (\Exception $exception)
        {
            throw $exception;
        }
        finally
        {
            if ($connectionInstance)
            {
                $pool->pushConnectionInstance($connectionInstance);
            }
        }

    }
}