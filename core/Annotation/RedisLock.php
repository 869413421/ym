<?php

namespace Core\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation()
 * @Target({"METHOD"})
 */
class RedisLock
{
    public $prefix = 'lock';
    public $key = '';
    public $retry = 3;
}