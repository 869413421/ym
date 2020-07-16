<?php

use App\Controller\UserController;
use Core\BeanFactory;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/define.php';
BeanFactory::init();
$asUserController = BeanFactory::getBeans('testaaa');
$userController = BeanFactory::getBeans(UserController::class);
var_dump($userController);