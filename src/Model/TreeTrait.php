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

use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;

/**
 * @property int|string $pid 上级
 * @property string $node 树形节点
 * @property BelongsTo|static $parent 上级模型
 * @property null|Collection|HasMany|static[] $children 下级模型集合
 * @property null|Collection|static[] $ancestors 祖辈模型集合
 * @property null|Collection|static[] $descendants 子孙模型集合
 * @method static static|Builder queryAncestor(?string $node = null) 查询父辈节点
 * @method static static|Builder queryDescendant(?string $node = null) 查询子孙节点
 */
trait TreeTrait
{
    /**
     * 上级.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'pid');
    }

    /**
     * 子级.
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'pid');
    }

    /**
     * 祖先节点.
     */
    public function ancestors(): Collection
    {
        return $this->queryAncestor()->get();
    }

    /**
     * 子孙节点.
     */
    public function descendants(): Collection
    {
        return $this->queryDescendant()->get();
    }

    /**
     * 查询子孙节点.
     */
    public function scopeQueryDescendant(Builder $query, ?string $node = null): void
    {
        /* @var Builder $query */
        ! $node && $node = $this->node . $this->id;
        $query->where('node', 'like', $node . '_%');
    }

    /**
     * 查询祖先节点.
     */
    public function scopeQueryAncestor(Builder $query, ?string $node = null): void
    {
        /* @var Builder $query */
        $ids = explode('_', $node ?: $this->node);
        $query->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
    }
}
