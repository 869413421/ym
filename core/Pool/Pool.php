<?php

namespace Core\Pool;

use Swoole\Coroutine\Channel;
use Swoole\Timer;


abstract class Pool
{
    private $poolMin;

    private $poolMax;

    private $channel;

    private $connectionCount = 0;

    private $timeOut;

    abstract protected function createConnectionInstance();

    public function __construct($min = 5, $max = 10, $timeOut = 10)
    {
        $this->poolMin = $min;
        $this->poolMax = $max;
        $this->channel = new Channel($this->poolMax);
        $this->timeOut = $timeOut;
    }

    public function initPool()
    {
        for ($i = 0; $i < $this->poolMin; $i++)
        {
            $this->addConnectionToPool();
        }

        //定时清理空闲链接
        Timer::tick(2000, function ()
        {
            $this->cleanPool();
        });
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getConnectionInstance()
    {
        //如果连接池为空，判断是否超出最大连接数，如果没有新建一个
        if ($this->channel->isEmpty())
        {
            if ($this->connectionCount < $this->poolMax)
            {
                $this->addConnectionToPool();
            }
            else
            {
                $this->channel->pop(5);
            }
        }
        $instance = $this->channel->pop();
        $instance->useTime = time();
        return $instance;
    }

    /**
     * 回收链接
     * @param $instance
     * @return bool
     */
    public function pushConnectionInstance($instance)
    {
        if ($instance)
        {
            return $this->channel->push($instance);
        }
    }

    /**
     * 新建连接到连接池
     */
    public function addConnectionToPool()
    {
        try
        {
            $this->connectionCount++;
            $instance = new \stdClass();
            $instance->useTime = time();
            $instance->instance = $this->createConnectionInstance();
            $this->channel->push($instance);
        }
        catch (\Exception $exception)
        {
            $this->connectionCount--;
            throw $exception;
        }
    }

    public function cleanPool()
    {
        if ($this->channel->length() < (int)$this->poolMax * 0.6)
            return;

        $newPool = [];
        while (true)
        {
            if ($this->channel->isEmpty())
            {
                break;
            }

            $instance = $this->channel->pop(0.1);
            //如果当前链接数大于最小链接池，并且链接已经超过超时时间
            if ($this->connectionCount > $this->poolMin && (time() - $instance->useTime) > $this->timeOut)
            {
                $instance = null;
                $this->connectionCount--;
            }
            else
            {
                $newPool[] = $instance;
            }
        }

        //将没过期的重新放置到链接池
        foreach ($newPool as $newInstance)
        {
            $this->channel->push($newInstance);
        }
    }
}