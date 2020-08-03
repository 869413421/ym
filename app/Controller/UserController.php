<?php


namespace App\Controller;


use App\Model\User;
use Core\Annotation\Bean;
use Core\Annotation\DB;
use Core\Annotation\Redis;
use Core\Annotation\RedisLock;
use Core\Annotation\RequestMapping;
use Core\Annotation\Value;
use Core\Http\Request;
use Core\Http\Response;
use Core\Init\YmDB;
use Swoole\Coroutine\Channel;

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
     * @RequestMapping(url="/warmup")
     * @Redis(warmup="id",type="hash",prefix="User")
     */
    public function warmup()
    {
        return User::all();
    }

    /**
     * 缓存有序集合
     * @RequestMapping(url="/sortSetWarmup")
     * @Redis(type="sortSet",prefix="score",sortSetFiled="score",sortSetKey="stock",sortIdFiled="id")
     */
    public function sortSetWarmup()
    {
        return User::all();
    }

    /**
     * 缓存有序集合
     * @RequestMapping(url="/coroutineInsert")
     * @Redis(type="sortSet",prefix="score",sortSetFiled="score",sortSetKey="stock",sortIdFiled="id",coroutine=true)
     */
    public function coroutineInsert()
    {
        $channel = new Channel(3);
        $pageSize = 3;
        for ($i = 0; $i < 3; $i++)
        {
            go(function () use ($channel, $pageSize, $i)
            {
                $limit = $i * $pageSize;
                $data = $this->db->table('users')->take($pageSize)->skip($limit)->get()->map(function ($item)
                {
                    return (array)$item;
                })->toArray();
                $channel->push($data);
            });
        }
        return $channel;
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

    /**
     * @RequestMapping(url="/lock/{value}")
     * @RedisLock(key="#0")
     */
    public function lock($value)
    {
        $user = User::find($value);
        sleep(5);
        return $user;
    }

    /**
     * @RequestMapping(url="/lock2/{value}")
     * @RedisLock(key="#0")
     */
    public function lock2($value)
    {
        $user = User::find($value);
        return $user;
    }
}