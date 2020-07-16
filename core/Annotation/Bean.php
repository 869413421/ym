<?php


namespace Core\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 容器设置别名注解
 * @Annotation
 * @Target({"CLASS"})
 */
class Bean
{
    public $name = "";
}