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
}