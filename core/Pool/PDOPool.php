<?php

namespace Core\Pool;


class PDOPool extends DBPool
{
    public function createConnectionInstance()
    {
        $dbms = 'mysql';     //数据库类型
        $host = '47.94.155.227'; //数据库主机名
        $dbName = 'hyperf';    //使用的数据库
        $user = 'root';      //数据库连接用户名
        $passWord = 'Ym135168.';          //对应的密码
        $port = 3306;
        $dsn = "$dbms:host=$host;dbname=$dbName;port=$port";

        return new \PDO($dsn, $user, $passWord); //初始化一个PDO对象
    }

    /**
     * 获取POD链接
     * @return \PDO
     */
    public function getConnectionInstance()
    {
        return $this->channel->pop();
    }

    /**
     * 回收PDO链接
     * @param $instance
     * @return bool
     */
    public function pushConnectionInstance($instance)
    {
        return $this->channel->push($instance);
    }
}