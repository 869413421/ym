<?php

namespace Core\Init;

use Core\Pool\DBPool;

class PDOPool extends DBPool
{
    public function __construct($min = 5, $max = 10, $timeOut = 10)
    {
        global $GLOBALS_CONFIG;
        $poolConfig = $GLOBALS_CONFIG['databasePool']['default'];
        parent::__construct($poolConfig['min'], $poolConfig['max'], $poolConfig['timeOut']);
    }

    public function createConnectionInstance()
    {
        global $GLOBALS_CONFIG;
        $config = $GLOBALS_CONFIG['database']['default'];

        {
            $driver = $config['driver'];
            $host = $config['host'];
            $dbName = $config['database'];
            $userName = $config['username'];
            $passWord = $config['password'];
            $port = $config['port'];
            $dsn = "$driver:host=$host;dbname=$dbName;port=$port";
        }

        return new \PDO($dsn, $userName, $passWord); //初始化一个PDO对象
    }

}