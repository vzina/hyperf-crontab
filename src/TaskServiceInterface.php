<?php
/**
 * TaskServiceInterface.php
 * PHP version 7
 *
 * @package hyperf-skeleton
 * @author  weijian.ye
 * @contact yeweijia299@163.com
 * @link    https://github.com/vzina
 */
declare (strict_types=1);

namespace Vzina\Crontab;

use Hyperf\Crontab\Crontab;

interface TaskServiceInterface
{
    /**
     * 获取任务列表
     *
     * @return Crontab[]
     */
    public function getTaskList(): array;
}