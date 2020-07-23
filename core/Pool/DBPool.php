<?php

namespace Core\Pool;

use Swoole\Coroutine\Channel;


abstract class DBPool
{
    private $poolMin;

    private $poolMax;

    protected $channel;

    abstract protected function createConnectionInstance();

    public function __construct($min = 5, $max = 10)
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
    }
}