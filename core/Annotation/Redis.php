<?php

namespace Core\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation()
 * @Target({"METHOD"})
 */
class Redis
{
    public $key;
    public $value;
    public $type = 'string';
    public $prefix = 'cache_';
    public $ttl = 0;
    public $incrFiled = '';
    public $incrValue = 1;
    public $warmup = '';
    public $sortSetFiled = '';
    public $sortSetKey = '';
    public $sortIdFiled = '';
    public $coroutine = false;
}