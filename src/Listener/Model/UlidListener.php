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

use Ella123\HyperfUtils\Model\ModelTrait;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

#[Listener]
class UlidListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Creating::class,
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function process(object $event): void
    {
        if ($event instanceof Creating) {
            /** @var ModelTrait $model */
            $model = $event->getModel();
            if (! $model->ulid && $model->hasTableField('ulid')) {
                $model->setAttribute('ulid', strtolower((string) Str::ulid()));
            }
        }
    }
}
