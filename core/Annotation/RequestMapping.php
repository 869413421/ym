<?php


namespace Core\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestMapping
{
    public $url = '';
    public $method = [];
}