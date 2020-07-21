<?php

namespace Core\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class DB
{
    public $connection = 'default';
}