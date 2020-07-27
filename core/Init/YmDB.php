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

    private $transDb;

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
     * 开始事务
     * @return YmDB
     * @throws \Exception
     */
    public function beginTransaction()
    {
        return new self($this->pool->getConnectionInstance());
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        try
        {
            /** @var $db \PDO */
            $db = $this->transDb->db;
            if (!$db->commit())
            {
                throw new \PDOException($db->errorInfo(), $db->errorCode());
            }
        }
        catch (\PDOException $exception)
        {
            $this->rollBack();
            throw $exception;
        }
        finally
        {
            $this->pool->pushConnectionInstance($this->transDb);
        }
    }

    /**
     * 回滚
     */
    public function rollBack()
    {
        try
        {
            /** @var $db \PDO */
            $db = $this->transDb->db;
            $db->rollBack();
        }
        catch (\PDOException $exception)
        {
            throw $exception;
        }
        finally
        {
            $this->pool->pushConnectionInstance($this->transDb);
        }

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

    public function __construct($transDb = null)
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
            if ($transDb)
            {
                $this->transDb = $transDb;
            }
        }
        else
        {
            throw new \Exception('database config setting error');
        }
    }

    public function __call($methodName, $arguments)
    {
        $isTrans = false;
        if ($this->transDb)
        {
            $pdo = $this->transDb;
            $isTrans = true;
        }
        else
        {
            //从连接池获取一个连接
            $pdo = $this->pool->getConnectionInstance();
        }

        try
        {
            //如果连接池为空不处理
            if (!$pdo)
            {
                throw new \PDOException('connection error');
            }
            $dbConnection = $this->db->getConnection($this->connectionName)->setPdo($pdo->db);
            if ($isTrans)
            {
                if (!$dbConnection->getPdo()->inTransaction())
                {
                    $dbConnection->getPdo()->beginTransaction();
                };
            }
            $result = $dbConnection->$methodName(...$arguments);
            return $result;
        }
        catch (\PDOException $exception)
        {
            if ($isTrans)
            {
                $this->rollBack();
            }
            throw $exception;
        }
        finally
        {
            //放回连接池
            if (!$isTrans)
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