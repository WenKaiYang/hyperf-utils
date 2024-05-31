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
 * @property string $ulid ulid
 */
trait HasUlids
{
    public function setUlidAttribute(): void
    {
        $this->attributes['ulid'] = $this->newUniqueId();
    }

    /**
     * Generate a new ULID for the model.
     */
    public function newUniqueId(): string
    {
        return strtolower((string) Str::ulid());
    }
}
