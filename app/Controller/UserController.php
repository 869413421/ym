<?php


namespace App\Controller;


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
     * @RequestMapping(url="/test/{value1:\d+}/{value2:\d+}")
     */
    public function test(Request $request, int $value1, int $value2, Response $response)
    {
//        $response->withHttpStatus(404);
//        $response->redirect('https://www.baidu.com/');
        $result = $this->db->table('users')->first();
        return json_encode($result);
    }
}