<?php


namespace Ella123\HyperfUtils;


use Closure;
use Countable;
use DateInterval;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Job;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Engine\Contract\Http\V2\RequestInterface;
use Hyperf\Engine\Contract\Http\V2\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use PhpCsFixer\Cache\CacheInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use function Hyperf\Support\make;

/**
 * 是否为空
 */
function blank(mixed $value): bool
{
    if (is_null($value)) {
        return true;
    }

    if (is_string($value)) {
        return trim($value) === '';
    }

    if (is_numeric($value) || is_bool($value)) {
        return false;
    }

    if ($value instanceof Countable) {
        return count($value) === 0;
    }

    return empty($value);
}

/**
 * 过滤数组
 * @param  array  $array
 * @return array
 */
function arrayFilterFilled(array $array): array
{
    return array_filter($array, static fn($item) => !blank($item));
}


/**
 * 获取容器实例.
 */
function app(): ContainerInterface
{
    return ApplicationContext::getContainer();
}

/**
 * 日志组件.
 *
 * @param  string  $group  日志配置
 */
function logger(string $name = 'default', string $group = 'default'): LoggerInterface
{
    return make(LoggerFactory::class)
        ->get($name, $group);
}

/**
 * StdoutLogger.
 */
function stdoutLogger(): StdoutLoggerInterface
{
    return make(StdoutLoggerInterface::class);
}

/**
 * 获取缓存驱动.
 */
function cache()
{
    return make(CacheInterface::class);
}

/**
 * 触发事件.
 */
function event(object $event): void
{
    make(EventDispatcherInterface::class)->dispatch($event);
}

/**
 * 获取真实ip.
 */
function realIp(mixed $request = null): mixed
{
    $request = $request ?? make(RequestInterface::class);

    $ip = $request->getHeader('x-forwarded-for');

    if (empty($ip)) {
        $ip = $request->getHeader('x-real-ip');
    }

    if (empty($ip)) {
        $ip = $request->getServerParams()['remote_addr'] ?? '127.0.0.1';
    }

    if (is_array($ip)) {
        $ip = Arr::first($ip);
    }

    return Arr::first(explode(',', $ip));
}

/**
 * 投递队列.
 *
 * @param  Job  $job  异步Job
 * @param  int  $delay  延迟时间-秒
 * @param  string  $driver  消息队列驱动
 */
function asyncQueue(Job $job, int $delay = 0, string $driver = 'default'): void
{
    make(DriverFactory::class)->get($driver)->push($job, $delay);
}

/**
 * 页面重定向.
 *
 * @param  string  $url  跳转URL
 * @param  int  $status  HTTP状态码
 * @param  string  $schema  协议
 */
function redirect(string $url, int $status = 302, string $schema = 'http'): ResponseInterface
{
    return make(\Hyperf\HttpServer\Contract\ResponseInterface::class)
        ->redirect($url, $status, $schema);
}

/**
 * 数据缓存.
 *
 * @param  string  $key  缓存KEY
 * @param  null|DateInterval|int  $ttl  缓存时间
 */
function remember(string $key, null|DateInterval|int $ttl, Closure $closure): mixed
{
    if (!empty($value = cache()->get($key))) {
        return $value;
    }

    $value = $closure();

    cache()->set($key, $value, $ttl);

    return $value;
}

/**
 * 修改配置项.
 *
 * @param  string  $key  identifier of the entry to set
 * @param  mixed  $value  the value that save to container
 *
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function configSet(string $key, mixed $value): void
{
    app()->get(ConfigInterface::class)->set($key, $value);
}

/**
 * 如果给定条件为真，则抛出给定异常。
 *
 * @param  mixed  $condition  判断条件
 * @param  string|Throwable  $exception  指定异常信息(RuntimeException)|抛出异常
 * @param  mixed  ...$parameters  异常自定义参数
 *
 * @return mixed 返回条件数据
 * @throws Throwable
 */
function throwIf(mixed $condition, string|Throwable $exception = 'RuntimeException', ...$parameters): mixed
{
    if ($condition) {
        if (is_string($exception) && class_exists($exception)) {
            $exception = new $exception(...$parameters);
        }

        throw is_string($exception) ? new RuntimeException($exception) : $exception;
    }

    return $condition;
}

/**
 * 异常终止
 * @param  string  $message
 * @param  int  $code
 * @return void
 */
function abort(string $message, int $code = 500): void
{
    throw new RuntimeException(message: $message, code: $code);
}


/**
 * redis 用例.
 *
 * @param  string  $driver  redis实例
 */
function redis(string $driver = 'default'): RedisProxy
{
    return make(RedisFactory::class)->get($driver);
}

/**
 * 获取指定 annotation.
 *
 * @param  string  $class  查询类
 * @param  string  $method  查询方法
 * @param  string  $annotationTarget  指定注解类
 *
 * @throws AnnotationException
 */
function annotationCollector(
    string $class,
    string $method,
    string $annotationTarget
): AbstractAnnotation {
    $methodAnnotation = AnnotationCollector::getClassMethodAnnotation(
        $class,
        $method
    )[$annotationTarget] ?? null;

    if ($methodAnnotation instanceof $annotationTarget) {
        return $methodAnnotation;
    }

    $classAnnotation = AnnotationCollector::getClassAnnotations($class)[$annotationTarget] ?? null;
    if (!$classAnnotation instanceof $annotationTarget) {
        throw new AnnotationException("Annotation {$annotationTarget} couldn't be collected successfully.");
    }
    return $classAnnotation;
}
