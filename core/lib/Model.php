<?php


namespace Core\lib;


use Core\BeanFactory;
use Core\Init\YmDB;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public function __call($method, $parameters)
    {
        return $this->invoke(function () use ($method, $parameters)
        {
            return parent::__call($method, $parameters); // TODO: Change the autogenerated stub
        });
    }

    public function save(array $options = [])
    {
        return $this->invoke(function () use ($options)
        {
            return parent::save($options);
        });
    }

    public static function __callStatic($method, $parameters)
    {
        return self::invoke(function () use ($method, $parameters)
        {
            return parent::__callStatic($method, $parameters); // TODO: Change the autogenerated stub
        });

    }

    /**
     * 从连接池里面更改连接
     * @param callable $callback
     * @return mixed
     */
    private static function invoke(callable $callback)
    {
        /** @var $ymDB YmDB */
        $ymDB = clone BeanFactory::getBeans(YmDB::class);
        $connectionInstance = $ymDB->getConnection();
        try
        {
            return $callback();
        }
        catch (\PDOException $exception)
        {
            throw $exception;
        }
        finally
        {
            $ymDB->releaseConnection($connectionInstance);
        }
    }
}