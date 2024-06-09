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
 * @property string $title 标题
 * @method static static|Builder queryTitle(?string $title)
 */
trait HasTitle
{
    /**
     * 模糊查询标题.
     */
    public function scopeQueryTitle(Builder $query, ?string $title = null): void
    {
        $query->when($title, fn ($query) => $query->where(static::getTableName() . '.title', 'like', '%' . $title . '%'));
    }

    public static function findByTitle(string $title, $columns = ['*']): ?static
    {
        return static::query()
            ->where(static::getTableName() . '.title', $title)
            ->first(columns: $columns);
    }
}
