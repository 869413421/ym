<?php

namespace Core\Annotation;


class RedisLock
{
    public $prefix = 'lock';
    public $key = '';
    public $retry = 3;
}