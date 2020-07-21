<?php

namespace Core\Annotation\AnnotationHandler;

use Core\Annotation\DB;
use Core\BeanFactory;
use Core\Init\YmDB;

return [
    DB::class => function (\ReflectionProperty $property, $instance, $self)
    {
        //判断当前注解是否是默认数据库连接，如果不是，克隆新对象放置到容器当中
        if ($self->connection !== 'default')
        {
            $beanName = YmDB::class . '_' . $self->connection;
            $ymDB = BeanFactory::getBeans($beanName);
            if (!$ymDB)
            {
                $ymDB = clone BeanFactory::getBeans(YmDB::class);
                $ymDB->setConnectionName($self->connection);
                BeanFactory::setBeans($beanName, $ymDB);
            }
        }
        else
        {
            $ymDB = BeanFactory::getBeans(YmDB::class);
        }

        $property->setAccessible(true);
        $property->setValue($instance, $ymDB);
        return $instance;
    }
];
