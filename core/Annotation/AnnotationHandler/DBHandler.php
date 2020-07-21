<?php

namespace Core\Annotation\AnnotationHandler;

use Core\Annotation\DB;
use Core\BeanFactory;
use Core\Init\YmDB;

return [
    DB::class => function (\ReflectionProperty $property, $instance, $self)
    {
        $ymDB = BeanFactory::getBeans(YmDB::class);
        $property->setAccessible(true);
        $property->setValue($instance, $ymDB);
        return $instance;
    }
];
