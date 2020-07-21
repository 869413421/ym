<?php

namespace Core\Init;

use Core\Annotation\Bean;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * @Bean
 * @method \Illuminate\Database\Query\Builder table(\Closure | \Illuminate\Database\Query\Builder | string $table, string | null $as = null, string | null $connection = null)
 */
class YmDB
{
    private $db;

    public function __construct()
    {
        global $GLOBALS_CONFIG;
        if (isset($GLOBALS_CONFIG['database']) && isset($GLOBALS_CONFIG['database']['default']))
        {
            $this->db = new DB();
            $this->db->addConnection($GLOBALS_CONFIG['database']['default']);
            $this->db->setAsGlobal();
            $this->db->bootEloquent();
        }
        else
        {
            throw new \Exception('database config setting error');
        }
    }

    public function __call($methodName, $arguments)
    {
        return $this->db->$methodName(...$arguments);
    }

    public static function __callStatic($methodName, $arguments)
    {
        return DB::$methodName(...$arguments);
    }
}