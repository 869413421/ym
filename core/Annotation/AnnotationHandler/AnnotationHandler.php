<?php


namespace Core\Annotation\AnnotationHandler;

use Core\Annotation\Bean;
use Core\Annotation\Value;
use DI\Container;

return [
    //容器对象别名注解
    Bean::class => function ($instance, Container $container, $annotationSelf)
    {
        //获取到注解对象的属性
        $vars = get_object_vars($annotationSelf);

        //如果对象中设置了名称
        if (isset($vars['name']) && $vars['name'] != '')
        {
            $beanName = $vars["name"];
        }
        else
        {
            $classArr = explode("\\", get_class($instance));
            $beanName = end($classArr);
        }

        $container->set($beanName, $instance);
    },
    //读取配置文件注解
    Value::class => function (\ReflectionProperty $property, $instance, $annotationSelf)
    {
        $env = parse_ini_file(ROOT_PATH . "/env");

        if (!isset($env[$annotationSelf->name]) || $annotationSelf->name == "")
        {
            return $instance;
        }
        $property->setValue($instance, $env[$annotationSelf->name]);
        return $property;
    }
];