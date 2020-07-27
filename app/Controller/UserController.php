<?php


namespace App\Controller;


use App\Model\Post;
use App\Model\User;
use Core\Annotation\Bean;
use Core\Annotation\DB;
use Core\Annotation\RequestMapping;
use Core\Annotation\Value;
use Core\Http\Request;
use Core\Http\Response;
use Core\Init\YmDB;

/**
 * @Bean(name="testaaa")
 */
class UserController
{
    /**
     * @var string
     * @Value(name="version")
     */
    public $version = '';

    /**
     * @DB
     * @var YmDB
     */
    private $db;

    /**
     * @DB(connection="db2")
     * @var YmDB
     */
    private $db2;

    /**
     * @RequestMapping(url="/test/{value1:\d+}/{value2:\d+}")
     */
    public function test(Request $request, int $value1, int $value2, Response $response)
    {
        return User::all();
        $db = $this->db->beginTransaction();
        $result = $db->table('user_favorite_products')->insertGetId([
            'user_id' => 2,
            'product_id' => 1
        ]);
        $user = $db->table('users')->find(1);
        $db->table('users')->where('id', 1)->update(['name' => null]);
        $db->commit();
        return [
            'user' => $user,
            'result' => $result
        ];
    }
}