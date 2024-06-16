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

namespace Ella123\HyperfUtils\Enum;

trait EnumTrail
{
    public static function values(): array
    {
        $values = [];
        foreach (static::cases() as $case) {
            $values[] = $case->value;
        }
        return $values;
    }

    public static function names(): array
    {
        $names = [];
        foreach (static::cases() as $case) {
            $names[] = $case->name;
        }
        return $names;
    }

    public static function options(): array
    {
        $options = [];
        foreach (static::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => $case->label($case->value),
            ];
        }
        return $options;
    }

    public static function label($value): string
    {
        return _('enum.' . $value);
    }

    public static function keyToValue(): array
    {
        $items = [];
        foreach (static::cases() as $case) {
            $items[$case->value] = $case->name;
        }
        return $items;
    }
}
