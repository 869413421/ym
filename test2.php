<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/define.php';

$redis = new Redis();
$redis->connect('47.94.155.227', 6378);

$luaScript = <<<SCRIPT
      return redis.call(KEYS[1],KEYS[2],ARGV[1]);
SCRIPT;

$s = $redis->eval($luaScript, array('set', 'test_name', 'first'), 2);
var_dump($s);