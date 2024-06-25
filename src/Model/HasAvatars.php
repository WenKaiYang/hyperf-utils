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

use Ella123\HyperfGenerateAvatar\AvatarUtils;
use function Ella123\HyperfUtils\isURL;

/**
 * @property string $avatar 头像
 */
trait HasAvatars
{
    public function getAvatarAttribute(): string
    {
        return empty($this->original['avatar'])
            ? $this->gravatar(140)
            : $this->original['avatar'];
    }

    public function gravatar(int $size = 100, ?string $username = null): string
    {
        !$username && $username = $this->name
            ?: $this->username
                ?: $this->nickname
                    ?: md5((string)$this->getKey());

        return AvatarUtils::generateAvatar(username: $username, size: (int)$size)->toDataUri();
    }

    public function setAvatarAttribute($value): void
    {
        $this->attributes['avatar'] = isURL($value) ? $value : '';
    }
}
