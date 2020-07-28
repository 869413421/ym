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
    public $type;
}