<?php

namespace Core\Init;

use Core\Pool\DBPool;

class PDOPool extends DBPool
{
    public function createConnectionInstance()
    {
        $dbms = 'mysql';     //数据库类型
        $host = '47.94.155.227'; //数据库主机名
        $dbName = 'test';    //使用的数据库
        $user = 'root';      //数据库连接用户名
        $passWord = 'Ym135168.';          //对应的密码
        $port = 3306;
        $dsn = "$dbms:host=$host;dbname=$dbName;port=$port";

        return new \PDO($dsn, $user, $passWord); //初始化一个PDO对象
    }

}