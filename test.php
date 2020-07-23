<?php

use App\Controller\UserController;
use Core\BeanFactory;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/define.php';

use Swoole\Coroutine\WaitGroup;
use Swoole\Runtime;

Runtime::enableCoroutine(true);
Co\run(function ()
{
    $wg = new WaitGroup();
    $pool = new \Core\Pool\PDOPool();
    $pool->initPool();
    $stime = microtime(true); #获取程序开始执行的时间
    $result = [];
    for ($i = 5; $i > 0; $i--)
    {
        $wg->add();
        go(function () use ($pool, $wg, &$result)
        {
            $connection = $pool->getConnectionInstance();
            defer(function () use ($pool, $connection)
            {
                $pool->pushConnectionInstance($connection);
            });
            $result[] = $connection->query('select sleep(10);');
            $wg->done();
        });

    }
    for ($i = 0; $i < 6; $i++)
    {
        $wg->add();
        go(function () use ($pool, $wg, &$result)
        {
            $connection = $pool->getConnectionInstance();
            defer(function () use ($pool, $connection)
            {
                $pool->pushConnectionInstance($connection);
            });
            $result[] = $connection->query('select * form users limit 1;');
            $wg->done();
        });
    }
    $wg->wait();
//    var_dump($result);
    $etime = microtime(true); #获取程序执行结束的时间
    $total = $etime - $stime;   #计算差值
    echo "<br />{$total} times";
    while (true)
    {
        Swoole\Coroutine::sleep(1);
    }
});

