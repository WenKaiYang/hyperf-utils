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
use Hyperf\Database\Model\Builder;

/**
 * @property int $published 是否发布
 * @property Carbon $published_at 发布时间
 * @property Carbon $publishedAt 发布时间
 * @property ?Carbon $expired_at 过期时间
 * @property ?Carbon $expiredAt 过期时间
 * @method static static|Builder queryPublished(?string $published_at = null)
 * @method static static|Builder queryPublishedExpired(?string $published_at = null, ?string $expired_at = null)
 */
trait HasPublished
{
    public function scopeQueryPublished(
        Builder $query,
        ?string $published_at = null
    ): void {
        $query->where(static::getTableName() . '.published', true)
            ->where(static::getTableName() . '.published_at', '<=', Carbon::parse($published_at));
    }

    /**
     * 必须包含过期时间($expired_at).
     */
    public function scopeQueryPublishedExpired(
        Builder $query,
        ?string $published_at = null,
        ?string $expired_at = null
    ): void {
        /** @var static $query */
        $query->queryPublished($published_at)
            ->where(function ($query) use ($expired_at) {
                $query->where(static::getTableName() . '.expired_at', '>=', Carbon::parse($expired_at))
                    ->orWhereNull(static::getTableName() . '.expired_at');
            });
    }
}
