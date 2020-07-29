<?php

namespace Core\Init;


use Core\Annotation\Bean;
use Core\BeanFactory;

/**
 * @Bean()
 * @method static bool set(string $key, string $value)
 * @method static string get(string $key)
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