<?php


namespace App\Controller;


use Core\Annotation\Bean;
use Core\Annotation\RequestMapping;
use Core\Annotation\Value;
use Core\Http\Request;
use Core\Http\Response;

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
     * @RequestMapping(url="/test/{value1:\d+}/{value2:\d+}")
     */
    public function test(Request $request, int $value1, int $value2, Response $response)
    {
//        $response->withHttpStatus(404);
        $response->redirect('https://www.baidu.com/');
        return $value1 + $value2;
    }
}