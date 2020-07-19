<?php


namespace Core\Util;


class FileUtil
{
    public static function getFileMd5($dir, $ignore)
    {
        $files = glob($dir);
        $result = [];
        foreach ($files as $file)
        {
            if (is_dir($file) && strpos($file, $ignore) === false)
            {
                $result[] = self::getFileMd5($file . "/*", $ignore);
            }
            else if (pathinfo($file)["extension"] === "php")
            {
                $result[] = md5_file($file);
            }
        }
        return md5(implode("", $result));
    }
}