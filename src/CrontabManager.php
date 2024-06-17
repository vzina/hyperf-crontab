<?php
/**
 * CrontabManager.php
 * PHP version 7
 *
 * @package hyperf-skeleton
 * @author  weijian.ye
 * @contact yeweijia299@163.com
 * @link    https://github.com/vzina
 */
declare (strict_types=1);

namespace Vzina\Crontab;

use Hyperf\Di\Annotation\Inject;

class CrontabManager extends \Hyperf\Crontab\CrontabManager
{
    /**
     * @var TaskServiceInterface
     * @Inject()
     */
    protected $taskService;
    
    public function getCrontabs(): array
    {
        return array_merge($this->crontabs, $this->taskService->getTaskList());
    }
}