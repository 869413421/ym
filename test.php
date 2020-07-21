<?php

use App\Controller\UserController;
use Core\BeanFactory;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/define.php';
$db = new \Core\Init\YmDB();
$query = $db->table('users');
$result = $query->where('id', 1)->first();
var_dump($result);
