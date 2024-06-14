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

use Ella123\HyperfUtils\Model\TreeTrait;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Restored;
use Hyperf\Database\Model\Events\Restoring;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Events\Saving;
use Hyperf\Database\Model\Events\Updating;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use ReflectionClass;

#[Listener]
class TreeListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Creating::class,
            Updating::class,
            Saving::class,
            Saved::class,
            Deleting::class,
            Deleted::class,
            Restoring::class,
            Restored::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof Event) {
            $model = $event->getModel();
            $traits = (new ReflectionClass($model))->getTraitNames();
            if (! in_array(TreeTrait::class, array_keys($traits))) {
                // 排除 其他类型
                return;
            }

            // 保存之前
            if ($event instanceof Saving) {
                /** @var TreeTrait $model */
                if ($model->pid && $model->parent) {
                    $model->node = $model->parent->node . $model->pid . '_';
                } else {
                    $model->pid = 0;
                    $model->node = '_';
                }
            }

            // 删除之后
            if ($event instanceof Deleted) {
                /* @var TreeTrait $model */
                $model->queryDescendant()->delete();
            }
        }
    }
}
