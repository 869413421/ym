<?php
define("ROOT_PATH", dirname(dirname(__DIR__)));
$GLOBALS_CONFIG = [
    'database' => require_once __DIR__ . '/database.php',
];