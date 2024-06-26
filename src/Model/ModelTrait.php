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

namespace Ella123\HyperfUtils\Model;

use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Snowflake\Concern\Snowflake;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\StrCache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;

use function Ella123\HyperfUtils\remember;
use function Ella123\HyperfUtils\snowflakeId;

/**
 * @method getAttributes()
 * @method setAttribute(string $string, $value)
 */
trait ModelTrait
{
    /**
     * 获取当前模型属性(兼容蛇形或驼峰).
     * @param mixed $key
     */
    public function getAttribute($key)
    {
        return parent::getAttribute($key)
            ?? parent::getAttribute(StrCache::snake($key))
            ?? parent::getAttribute(StrCache::camel($key));
    }

    /**
     * 获取数据库表 字段.
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public static function getTableFields(): array
    {
        return array_keys(static::getTableColumns());
    }

    /**
     * 是否存在表格字段.
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public static function hasTableField(string $filed): bool
    {
        return in_array($filed, static::getTableFields());
    }

    /**
     * 获取数据库表 列信息.
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getTableColumns(): array
    {
        $key = 'tableColumns:' . md5(static::class);
        return (array) remember($key, 60, function () {
            $items = [];
            foreach (Db::select('SHOW COLUMNS FROM ' . static::getTableName(true)) as $row) {
                $items[$row->Field] = $row;
            }
            return $items;
        });
    }

    /**
     * 获取数据库 表名.
     */
    public static function getTableName(bool $prefix = false): string
    {
        return ($prefix ? static::getPrefix() : '') . static::getInstance()
            ->getTable();
    }

    /**
     * 获取数据库表 前缀。
     */
    public static function getPrefix(): string
    {
        return (string) static::query()->getConnection()->getTablePrefix();
    }

    /**
     * 当前模型实例。
     */
    public static function getInstance(): Model|static
    {
        return new static();
    }

    /**
     * 是否包含字段.
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public static function isField(string $field): bool
    {
        return isset(static::getTableColumns()[$field]);
    }

    /**
     * 批量插入数据。
     */
    public static function batchDataInsert(array $items, null|array|Model $parent = null, array $common = []): bool
    {
        return static::insert(static::getBatchData($items, $parent, $common));
    }

    /**
     * 获取批量数据。
     */
    public static function getBatchData(array $items, null|array|Model $parent = null, array $common = []): array
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = static::fillData($common + $item, $parent);
        }

        return $data;
    }

    /**
     * 模型填充数据.
     */
    public static function fillData(array $attributes, null|array|Model $parent = null): array
    {
        $model = static::getInstance();
        // 模型填充
        $attributes = $model->fill($attributes)->getAttributes();
        // 判断主键是否自增
        if (! $model->getIncrementing()) {
            $traits = (new ReflectionClass($model))->getTraitNames();
            if (in_array(Snowflake::class, array_keys($traits))) {
                // 是否雪花id
                if (! $model->getKey()) {
                    $attributes[$model->getKeyName()] = snowflakeId();
                }
            }
        }

        // 添加时间
        if ($model->usesTimestamps()) {
            $datetime = Carbon::now();
            if (empty($attributes['created_at'])) {
                $attributes['created_at'] = $datetime->toDateTimeString();
            }
            if (empty($attributes['updated_at'])) {
                $attributes['updated_at'] = $datetime->toDateTimeString();
            }
        }
        // ulid
        if (! $model->ulid && $model->hasTableField('ulid')) {
            $attributes['ulid'] = strtolower((string) Str::ulid());
        }
        // 添加父级
        if (isset($parent['id'], $parent['node'])) {
            $attributes['pid'] = $parent['id'];
            $attributes['node'] = $parent['node'] . $parent['id'] . '_';
        }

        return $attributes;
    }

    /**
     * Generate a new ULID for the model.
     */
    public static function generateUlid(): string
    {
        return strtolower((string) Str::ulid());
    }
}
