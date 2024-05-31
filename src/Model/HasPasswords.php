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
 * @property string $password 密码
 */
trait HasPasswords
{
    public function setPasswordAttribute($value): void
    {
        if ($value && mb_strlen($value) < 30) {
            $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
        }
    }
}
