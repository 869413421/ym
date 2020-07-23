<?php

namespace Core\Pool;

use Swoole\Coroutine\Channel;


abstract class DBPool
{
    private $poolMin;

    private $poolMax;

    private $channel;

    private $connectionCount;

    abstract protected function createConnectionInstance();

    public function __construct($min = 5, $max = 6)
    {
        $this->poolMin = $min;
        $this->poolMax = $max;
        $this->channel = new Channel($this->poolMax);
    }

    public function initPool()
    {
        for ($i = 0; $i < $this->poolMin; $i++)
        {
            $connectionInstance = $this->createConnectionInstance();
            $this->channel->push($connectionInstance);
        }
        $this->connectionCount = $this->poolMin;
    }

    /**
     * 获取POD链接
     * @return \PDO
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
                echo 'pool is null' . PHP_EOL;
            }
        }
        return $this->channel->pop();
    }

    /**
     * 回收PDO链接
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
            $this->pushConnectionInstance($this->createConnectionInstance());
        }
        catch (\PDOException $exception)
        {
            $this->connectionCount--;
            throw $exception;
        }
    }
}