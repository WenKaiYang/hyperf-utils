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

/**
 * @property string $ulid ulid
 */
trait HasUlid
{
    public static function findByUlid(string $ulid, array $columns = ['*']): ?static
    {
        return static::where(static::getTableName() . '.ulid', $ulid)->first($columns);
    }

    public static function findByUlidOrFail(string $ulid, array $columns = ['*']): static
    {
        return static::where(static::getTableName() . '.ulid', $ulid)->firstOrFail($columns);
    }
}
