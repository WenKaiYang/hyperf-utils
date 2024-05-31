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

use Hyperf\Stringable\Str;

/**
 * @property string $avatar 头像
 */
trait HasAvatars
{
    public function getAvatarAttribute(): string
    {
        return $this->avatar ?? $this->gravatar(140);
    }

    public function gravatar($size = '100'): string
    {
        $hash = md5(strtolower((string) ($this->email ?: $this->name ?: Str::random())));
        return "https://cdn.v2ex.com/gravatar/{$hash}?s={$size}";
    }

    public function setAvatarAttribute($value): void
    {
        if (! $value) {
            $value = $this->gravatar(140);
        }
        $this->attributes['avatar'] = $value;
    }
}
