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
 * @property string $code 标识代码
 * @method static static|Builder queryCode(?string $code)
 */
trait HasCode
{
    /**
     * 模糊查询名称.
     */
    public function scopeQueryCode(Builder $query, ?string $code = null): void
    {
        $query->when($code, fn ($query) => $query->where(static::getTableName() . '.code', 'like', '%' . $code . '%'));
    }

    public static function findByCode(string $code, $columns = ['*']): ?static
    {
        return static::query()
            ->where(static::getTableName() . '.code', $code)
            ->first(columns: $columns);
    }
}
