# 定时任务扩展功能

- 安装

```shell
composer require vzina/hyperf-crontab
```



- 定义任务服务

```php
use Hyperf\Crontab\Crontab;
use Vzina\Crontab\Schedule;
use Vzina\Crontab\ScheduleInterface;

class CustomSchedule extends Schedule implements ScheduleInterface
{
    /**
     * @inheritDoc
     */
    public function getTaskList(): array
    {
        $crontabs = parent::getTaskList();

        $tasks = []; // from db

        if (empty($tasks)) {
            return $crontabs;
        }

        return array_map(static function ($task) { // 格式化任务
            $cron = new Crontab();
            $cron->setName('cron_task_' . $task['id']);
            $cron->setRule($task['cron_spec']);
            if (! empty($task['concurrent'])) {
                $cron->setSingleton(true);
                $cron->setOnOneServer(true);
            }

            $cron->setType($task['task_type']);
            $cron->setCallback($task['command']);

            return $cron;
        }, $tasks) + $crontabs;
    }
}
```



- 配置使用

```php
# 使用自定义方式注册任务
#
# config/autoload/dependencies.php
return [
    \Vzina\Crontab\ScheduleInterface::class => \CustomSchedule::class,
    // other...
];

# 通过 config/crontabs.php 来定义定时任务，如配置文件不存在可自行创建：

CustomSchedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
CustomSchedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
CustomSchedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```



- 监听任务执行结果

```php
// 定义监听器
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Vzina\Crontab\Event\CrontabExecuted;

/**
 * @Listener()
 */
class CrontabExecutedListener implements ListenerInterface
{
    /**
     * @inheritDoc
     */
    public function listen(): array
    {
        return [
            CrontabExecuted::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function process(object $event)
    {
        if (! $event instanceof CrontabExecuted) {
            return;
        }

        // todo: 定义处理逻辑
        var_dump($event);
    }
}
```

