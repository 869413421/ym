<?php


namespace Core\Process;


use Core\Util\FileUtil;
use Swoole\Process;

/**
 * 自定义监控进程，用于热更新
 * Class MonitorProcess
 * @package Core\Process
 */
class MonitorProcess
{
    private $md5Value;

    public function run()
    {
        return new Process(function ()
        {
            cli_set_process_title('YM Monitor');
            while (true)
            {
                sleep(1);
                //检查app目录下文件是否有变化
                $md5Value = FileUtil::getFileMd5(ROOT_PATH . '/app/*', '/app/config');
                if (!$this->md5Value)
                {
                    $this->md5Value = $md5Value;
                    continue;
                }
                if (strcmp($md5Value, $this->md5Value) === 0)
                {
                    continue;
                }

                //文件有变化
                echo 'YM Reloading....' . PHP_EOL;
                $pid = (int)file_get_contents(ROOT_PATH . '/ym.pid');
                Process::kill($pid, SIGUSR1);
                $this->md5Value = $md5Value;
                echo 'YM ReloadSuccess...' . PHP_EOL;
            }
        });
    }
}