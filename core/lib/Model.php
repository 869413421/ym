<?php


namespace Core\lib;


use Core\BeanFactory;
use Core\Init\ConnectionPool;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public function getConnection()
    {
        /** @var $pool ConnectionPool * */
        $pool = BeanFactory::getBeans(ConnectionPool::class);
        return $pool->getConnectionInstance()->db;
    }
}