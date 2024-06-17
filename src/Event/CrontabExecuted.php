<?php
/**
 * CrontabExecuted.php
 * PHP version 7
 *
 * @package hyperf-skeleton
 * @author  weijian.ye
 * @contact yeweijia299@163.com
 * @link    https://github.com/vzina
 */
declare (strict_types=1);

namespace Vzina\Crontab\Event;

use Hyperf\Crontab\Crontab;

class CrontabExecuted
{
    /**
     * @var Crontab
     */
    public $crontab;

    /**
     * @var bool
     */
    public $isSuccess;

    /**
     * @var array
     */
    public $execTimes;

    /**
     * @var \Throwable
     */
    public $throwable;

    public function __construct(Crontab $crontab, bool $isSuccess, array $execTimes = [], ?\Throwable $throwable = null)
    {
        $this->crontab = $crontab;
        $this->isSuccess = $isSuccess;
        $this->throwable = $throwable;
        $this->execTimes = $execTimes;
    }
}