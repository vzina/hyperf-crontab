<?php
/**
 * TaskService.php
 * PHP version 7
 *
 * @package hyperf-skeleton
 * @author  weijian.ye
 * @contact yeweijia299@163.com
 * @link    https://github.com/vzina
 */
declare (strict_types=1);

namespace Vzina\Crontab;

class TaskService implements TaskServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getTaskList(): array
    {
        return [];
    }
}