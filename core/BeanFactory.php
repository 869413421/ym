<?php

namespace Core;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class BeanFactory
{
    /**
     * @var array
     */
    private static $env = [];

    /**
     * @var Container
     */
    private static $container;

    /**
     * 注解处理器
     * @var array
     */
    private static $annotationHandler = [];

    /**
     * 获取配置文件
     * @param string $key
     * @param string $default
     * @return mixed|string
     */
    private static function getEnv(string $key, $default = "")
    {
        if (isset(self::$env[$key]))
        {
            return self::$env[$key];
        }

        return $default;
    }

    /**
     * 初始化容器
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function init()
    {
        //初始化配置文件
        self::$env = parse_ini_file(ROOT_PATH . "/env");

        //初始化容器Builder
        $builder = new ContainerBuilder();
        //容器解析使用注解
        $builder->useAnnotations(true);
        //初始化容器
        self::$container = $builder->build();
        //获取到所有的注解处理器
        $handlers = glob(ROOT_PATH . '/core/Annotation/AnnotationHandler/*.php');

        foreach ($handlers as $handler)
        {
            self::$annotationHandler = array_merge(self::$annotationHandler, require_once($handler));
        }

        //设置注解扫描目录
        $loader = require __DIR__ . '/../vendor/autoload.php';
        AnnotationRegistry::registerLoader([$loader, 'loadClass']);

        //循环扫描目录文件
        $scanDirs = [
            ROOT_PATH . '/core/Init' => "Core\\",
            self::getEnv('SCAN_DIR', ROOT_PATH . "/app") => $scanRootNameSpace = self::getEnv('SCAN_ROOT', "App\\")
        ];

        foreach ($scanDirs as $scanDir => $scanRootNameSpace)
        {
            self::scanBeans($scanDir, $scanRootNameSpace);
        }

    }

    /**
     * 获取容器内的对象
     * @param $beanName
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public static function getBeans($beanName)
    {
        return self::$container->get($beanName);
    }

    /**
     * 获取目录下所有PHP文件
     * @param $dir
     * @return array
     */
    private static function getAllPHPFile($dir)
    {
        $result = [];
        $files = glob($dir . '/*');

        foreach ($files as $file)
        {
            if (is_dir($file))
            {
                $result = array_merge($result, self::getAllPHPFile($file));
            }
            else if (pathinfo($file)["extension"] === "php")
            {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * 扫描注解
     * @param $scanDir *需要扫描的目录
     * @param $scanRootNameSpace *需要处理类的命名空间前缀
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function scanBeans($scanDir, $scanRootNameSpace)
    {
        $files = self::getAllPHPFile($scanDir);

        foreach ($files as $file)
        {
            require_once $file;
        }

        //获取到需要处理注解的类
        $reader = new AnnotationReader();
        //获取所有已经加载的类
        foreach (get_declared_classes() as $class)
        {
            //如果类在配置的扫描命名空间下，处理注解
            if (strstr($class, $scanRootNameSpace))
            {
                $refClass = new \ReflectionClass($class);
                //获取到所有的类注解
                $classAnnotations = $reader->getClassAnnotations($refClass);
                foreach ($classAnnotations as $classAnnotation)
                {
                    if (!isset(self::$annotationHandler[get_class($classAnnotation)])) continue;
                    //找到注解对应的处理器
                    $handler = self::$annotationHandler[get_class($classAnnotation)];
                    //从容器中获取对象
                    $instance = self::$container->get($refClass->getName());
                    //处理属性注解
                    self::handlerPropertyAnnotation($instance, $refClass, $reader);
                    //处理方法注解
                    self::handlerMethodAnnotation($instance, $refClass, $reader);

                    $handler($instance, self::$container, $classAnnotation);
                }
            }
        }
    }

    /**
     * 处理属性注解
     * @param $instance *类实例化对象
     * @param \ReflectionClass $refClass *反射类对象
     * @param AnnotationReader $reader *注解阅读器
     */
    private static function handlerPropertyAnnotation(&$instance, \ReflectionClass $refClass, AnnotationReader $reader)
    {
        //获取到类的所有属性
        $properties = $refClass->getProperties();
        //循环处理属性的所有注解
        foreach ($properties as $property)
        {
            //获取到属性所有的注解
            $propertyAnnotations = $reader->getPropertyAnnotations($property);
            //处理所有的注解
            foreach ($propertyAnnotations as $annotation)
            {

                if (!isset(self::$annotationHandler[get_class($annotation)])) continue;
                //获取到对应的注解处理器
                $handler = self::$annotationHandler[get_class($annotation)];
                $handler($property, $instance, $annotation);
            }
        }
    }

    /**
     * 处理方法注解
     * @param $instance
     * @param \ReflectionClass $reflectionClass
     * @param AnnotationReader $reader
     */
    private static function handlerMethodAnnotation(&$instance, \ReflectionClass $reflectionClass, AnnotationReader $reader)
    {
        //获取到类的所有方法
        $methods = $reflectionClass->getMethods();

        foreach ($methods as $method)
        {
            //获取到所有方法注解
            $methodAnnotations = $reader->getMethodAnnotations($method);

            foreach ($methodAnnotations as $annotation)
            {
                if (!isset(self::$annotationHandler[get_class($annotation)])) continue;
                //获取到对应的注解处理器
                $handler = self::$annotationHandler[get_class($annotation)];
                $handler($method, $instance, $annotation);
            }
        }
    }


}