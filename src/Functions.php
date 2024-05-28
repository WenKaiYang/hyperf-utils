<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Ella123\HyperfUtils;

use Closure;
use Countable;
use DateInterval;
use Exception;
use GuzzleHttp\Client;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use Throwable;
use function Hyperf\Support\make;

/**
 * 是否空白.
 */
function isBlank(mixed $value): bool
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
 * 过滤数组.
 */
function arrayFilterFilled(array $array): array
{
    return array_filter($array, static fn($item) => !isBlank($item));
}

/**
 * 获取容器实例.
 */
function app(): ContainerInterface
{
    return ApplicationContext::getContainer();
}

/**
 * 协程上下文.
 * @param mixed|null $default
 */
function context(string $id, mixed $default = null): mixed
{
    return Context::getOrSet($id, $default ?: make($id));
}

/**
 * 日志组件.
 * @param string $group 日志配置
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function logger(string $name = 'default', string $group = 'default'): LoggerInterface
{
    return app()->get(LoggerFactory::class)
        ->get($name, $group);
}

/**
 * 控制台日志输出
 * StdoutLogger.
 *
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function stdoutLogger(): StdoutLoggerInterface
{
    return app()->get(StdoutLoggerInterface::class);
}

/**
 * 控制台日志输出(别名).
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function stdLogger(): StdoutLoggerInterface
{
    return stdoutLogger();
}

/**
 * redis 用例.
 *
 * @param string $driver redis实例
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function redis(string $driver = 'default'): RedisProxy
{
    return app()->get(RedisFactory::class)->get($driver);
}

/**
 * 获取缓存驱动.
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function cache(): CacheInterface
{
    return app()->get(CacheInterface::class);
}

/**
 * 记住数据.
 *
 * @param string $key 缓存KEY
 * @param null|DateInterval|int $ttl 缓存时间
 * @throws ContainerExceptionInterface
 * @throws InvalidArgumentException
 * @throws NotFoundExceptionInterface
 */
function remember(string $key, null|DateInterval|int $ttl, Closure $closure): mixed
{
    if (!empty($value = cache()->get($key))) {
        return $value;
    }

    $value = $closure();

    cache()->set(key: $key, value: $value, ttl: $ttl);

    return $value;
}

/**
 * 长期记得.
 * @throws ContainerExceptionInterface
 * @throws InvalidArgumentException
 * @throws NotFoundExceptionInterface
 */
function rememberForever(string $key, Closure $callback): mixed
{
    return remember(key: $key, ttl: null, closure: $callback);
}

/**
 * 获取真实ip.
 */
function realIp(mixed $request = null): string
{
    $request = $request ?? request();
    /** @var RequestInterface $request */
    return $request->getHeaderLine('X-Forwarded-For')
        ?: $request->getHeaderLine('X-Real-IP')
            ?: ($request->getServerParams()['remote_addr'] ?? '')
                ?: '127.0.0.1';
}

/**
 * 获取真实ip(别名).
 */
function ip(mixed $request = null): string
{
    return realIp($request);
}

/**
 * 请求对象
 */
function request(): RequestInterface
{
    return \Ella123\HyperfUtils\context(RequestInterface::class);
}

/**
 * 请求参数.
 */
function input(string $key, mixed $default = null)
{
    return request()->input(key: $key, default: $default);
}

/**
 * 投递队列.
 * @param Job $job 异步Job
 * @param int $delay 延迟时间-秒
 * @param string $driver 消息队列驱动
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function queue(Job $job, int $delay = 0, string $driver = 'default'): bool
{
    return app()->get(DriverFactory::class)->get($driver)->push($job, $delay);
}

/**
 * 投递队列(别名).
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function job(Job $job, int $delay = 0, string $driver = 'default'): bool
{
    return queue($job, $delay, $driver);
}

/**
 * 触发事件.
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function event(object $event): object
{
    return app()->get(EventDispatcherInterface::class)->dispatch($event);
}

/**
 * 触发事件(别名).
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function dispatch(object $event): object
{
    return event($event);
}

/**
 * 页面重定向.
 * @param string $url 跳转URL
 * @param int $status HTTP状态码
 * @param string $schema 协议
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function redirect(string $url, int $status = 302, string $schema = 'http'): ResponseInterface
{
    return \Ella123\HyperfUtils\context(\Hyperf\HttpServer\Contract\ResponseInterface::class)
        ->redirect($url, $status, $schema);
}

/**
 * 如果给定条件为真，则抛出给定异常。
 * @param mixed $condition 判断条件
 * @param string|Throwable $exception 指定异常信息(RuntimeException)|抛出异常
 * @param mixed ...$parameters 异常自定义参数
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
 * 异常终止.
 * @throws Exception
 */
function abort(Exception|string $message, int $code = 500): void
{
    throw is_string($message) ? new RuntimeException(message: $message, code: $code) : $message;
}

/**
 * 获取指定注释(Annotation).
 * @param string $class 查询类
 * @param string $method 查询方法
 * @param string $annotationTarget 指定注解类
 *
 * @throws AnnotationException
 */
function annotationCollector(
    string $class,
    string $method,
    string $annotationTarget
): AbstractAnnotation
{
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

/**
 * http客户端.
 */
function httpClient(array $options = []): Client
{
    return make(ClientFactory::class)->create($options);
}
