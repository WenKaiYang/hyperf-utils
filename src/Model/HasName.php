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

use Hyperf\Database\Model\Builder;

/**
 * @property string $name 名称
 * @method static static|Builder queryName(?string $name)
 */
trait HasName
{
    /**
     * 模糊查询名称.
     */
    public function scopeQueryName(Builder $query, ?string $name = null): void
    {
        $query->when($name, fn ($query) => $query->where(static::getTableName() . '.name', 'like', '%' . $name . '%'));
    }

    public static function findByName(string $name, $columns = ['*']): ?static
    {
        return static::query()
            ->where(static::getTableName() . '.name', $name)
            ->first(columns: $columns);
    }
}
