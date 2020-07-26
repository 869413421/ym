<?php

namespace Core\Init;

use Core\Annotation\Bean;
use Core\BeanFactory;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * @Bean
 * @method \Illuminate\Database\Query\Builder table(\Closure | \Illuminate\Database\Query\Builder | string $table, string | null $as = null, string | null $connection = null)
 */
class YmDB
{
    private $db;

    private $connectionName = 'default';

    /**
     * @var PDOPool
     */
    private $pool;

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }


    /**
     * @param $connectionName
     * @return $this
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
        return $this;
    }

    public function __construct()
    {
        global $GLOBALS_CONFIG;
        if (isset($GLOBALS_CONFIG['database']) && isset($GLOBALS_CONFIG['database']['default']))
        {
            $configs = $GLOBALS_CONFIG['database'];
            $this->db = new DB();
            foreach ($configs as $key => $value)
            {
                $this->db->addConnection(['driver' => 'mysql'], $key);
            }
            $this->db->setAsGlobal();
            $this->db->bootEloquent();
            $this->pool = BeanFactory::getBeans(PDOPool::class);
            $this->pool->initPool();
        }
        else
        {
            throw new \Exception('database config setting error');
        }
    }

    public function __call($methodName, $arguments)
    {
        //从连接池获取一个连接
        $pdo = $this->pool->getConnectionInstance();

        try
        {
            //如果连接池为空不处理
            if (!$pdo)
            {
                return null;
            }

            $this->db->getConnection($this->connectionName)->setPdo($pdo->db);
            return $this->db->$methodName(...$arguments);
        }
        catch (\PDOException $exception)
        {
            return null;
        }
        finally
        {
            //放回连接池
            if ($pdo)
            {
                $this->pool->pushConnectionInstance($pdo);
            }
        }

    }

    public static function __callStatic($methodName, $arguments)
    {
        return DB::$methodName(...$arguments);
    }
}