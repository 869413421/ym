<?php


namespace App\Controller;


use App\Model\Post;
use App\Model\User;
use Core\Annotation\Bean;
use Core\Annotation\DB;
use Core\Annotation\Redis;
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
     * @Redis(prefix="User",key="#1",ttl="3000",type="hash",incrFiled="score",incrValue="1")
     * @RequestMapping(url="/test/{value1:\d+}/{value2:\d+}")
     */
    public function test(Request $request, int $value1, int $value2, Response $response)
    {
        return User::find($value1);
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

    /**
     * @RequestMapping(url="/add")
     */
    public function add(Request $request, Response $response)
    {
        $user = new User();
        $user->name = 'xiaoming';
        $user->score = 99;
        $user->save();

        return $user->toArray();
    }

    /**
     * @RequestMapping(url="/update")
     */
    public function update(Request $request, Response $response)
    {
        $user = User::find(11);
        $user->name = 'xiaolonglong';
        $user->save();

        return $user->toArray();
    }

    /**
     * @RequestMapping(url="/delete")
     */
    public function delete(Request $request, Response $response)
    {
        $user = User::find(11);
        $user->delete();

        return 'delete success';
    }

    /**
     * @RequestMapping(url="/trans")
     */
    public function trans(Request $request, Response $response)
    {
        $db = $this->db->beginTransaction();
        $user = new User();
        $user->name = 'xiaoming';
        $user->score = 99;
        $user->save();

        $newUser = User::find(15);
        var_dump($db);
        if ($newUser != null)
        {
            $db->rollBack();
        }
        else
        {
            $db->commit();
        }
        return 'delete success';
    }
}