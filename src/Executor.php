<?php
/**
 * Executor.php
 * PHP version 7
 *
 * @package hyperf-skeleton
 * @author  weijian.ye
 * @contact yeweijia299@163.com
 * @link    https://github.com/vzina
 */
declare (strict_types=1);

namespace Vzina\Crontab;

use Carbon\Carbon;
use Closure;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Event\FailToExecute;
use Swoole\Timer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Vzina\Crontab\Event\CrontabExecuted;

class Executor extends \Hyperf\Crontab\Strategy\Executor
{
    public function execute(Crontab $crontab)
    {
        if (! $crontab instanceof Crontab || ! $crontab->getExecuteTime()) {
            return;
        }

        $diff = $crontab->getExecuteTime()->diffInRealSeconds(new Carbon());
        $runnable = null;
        switch ($crontab->getType()) {
            case 'closure':
                /** @var \Opis\Closure\SerializableClosure $runnable */
                $runnable = $crontab->getCallback();
                break;
            case 'callback':
                [$class, $method] = $crontab->getCallback();
                $parameters = $crontab->getCallback()[2] ?? null;
                if ($class && $method && class_exists($class) && method_exists($class, $method)) {
                    $runnable = static function () use ($class, $method, $parameters) {
                        $instance = make($class);
                        if ($parameters && is_array($parameters)) {
                            $instance->{$method}(...$parameters);
                        } else {
                            $instance->{$method}();
                        }
                    };
                }
                break;
            case 'command':
                $input = make(ArrayInput::class, [$crontab->getCallback()]);
                $output = make(NullOutput::class);
                /** @var Application $application */
                $application = $this->container->get(ApplicationInterface::class);
                $application->setAutoExit(false);
                $application->setCatchExceptions(false);
                $runnable = static function () use ($application, $input, $output) {
                    if ($application->run($input, $output) !== 0) {
                        throw new RuntimeException('Crontab task failed to execute.');
                    }
                };
                break;
            case 'eval':
                $runnable = static fn() => eval($crontab->getCallback());
                break;
            default:
                throw new InvalidArgumentException(sprintf('Crontab task type [%s] is invalid.', $crontab->getType()));
        }

        $runnable = function () use ($crontab, $runnable) {
            $runnable = $this->catchToExecute($crontab, $runnable);
            $this->decorateRunnable($crontab, $runnable)();
        };

        $runnable && Timer::after($diff > 0 ? $diff * 1000 : 1, $runnable);
    }

    protected function catchToExecute(Crontab $crontab, ?callable $runnable): Closure
    {
        return function () use ($crontab, $runnable) {
            $throwable = null;
            $startTime = microtime(true);
            try {
                $result = true;
                if (! $runnable) {
                    throw new InvalidArgumentException('The crontab task is invalid.');
                }
                $runnable();
            } catch (Throwable $throwable) {
                $result = false;
                $this->dispatcher && $this->dispatcher->dispatch(new FailToExecute($crontab, $throwable));
            } finally {
                $this->logResult($crontab, $result);

                $this->dispatcher && $this->dispatcher->dispatch(new CrontabExecuted(
                    $crontab, $result, [$startTime, microtime(true)], $throwable
                ));
            }
        };
    }
}
