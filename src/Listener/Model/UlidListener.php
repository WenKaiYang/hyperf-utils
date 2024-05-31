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

namespace Ella123\HyperfUtils\Listener\Model;

use Ella123\HyperfUtils\Model\HasUlids;
use Ella123\HyperfUtils\Model\ModelTrait;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Stringable\Str;

#[Listener]
class UlidListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Creating::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof Creating) {
            $model = $event->getModel();
            $attributes = $model->getAttributes();
            // ulid
            if (array_key_exists('ulid', $attributes) && ! $attributes['ulid']) {
                /* @var ModelTrait|HasUlids $model */
                $model->ulid = strtolower((string) Str::ulid());
            }
        }
    }
}
