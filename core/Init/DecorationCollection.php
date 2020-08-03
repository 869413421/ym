<?php

namespace Core\Init;


use Core\Annotation\Bean;

/**
 * 装饰收集器
 * @Bean()
 */
class DecorationCollection
{
    public $decorationSet = [];

    public function exec(\ReflectionMethod $refMethod, $instance, $params)
    {
        $key = get_class($instance) . '::' . $refMethod->getName();

        if (isset($this->decorationSet[$key]))
        {
            //通过装饰方法调用
            $callBack = $this->decorationSet[$key];
            return $callBack($refMethod->getClosure($instance))($params);
        }
        //如果没有找到装饰器直接通过反射的方式调用方法
        return $refMethod->invokeArgs($instance, $params);
    }
}