<?php
/**
 * Schedule.php
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
use Opis\Closure\SerializableClosure;
use Closure;

class Schedule implements ScheduleInterface
{
    public const ROUTE = BASE_PATH . '/config/crontabs.php';

    /**
     * @var Crontab[]
     */
    protected static array $crontabs = [];

    public function __construct()
    {
        if (is_file(self::ROUTE)) {
            require_once self::ROUTE;
        }
    }

    public static function command(string $command, array $arguments = []): Crontab
    {
        $arguments = array_merge(['command' => $command], $arguments);

        return tap(new Crontab(), fn ($crontab) => static::$crontabs[] = $crontab)
            ->setType('command')
            ->setCallback($arguments);
    }

    public static function call(callable $callable): Crontab
    {
        $type = 'callback';
        if ($callable instanceof Closure) {
            $type = 'closure';
            $callable = new SerializableClosure($callable);
        }

        return tap(new Crontab(), fn ($crontab) => static::$crontabs[] = $crontab)
            ->setType($type)
            ->setCallback($callable);
    }

    /**
     * @inheritDoc
     */
    public function getTaskList(): array
    {
        return static::$crontabs;
    }
}