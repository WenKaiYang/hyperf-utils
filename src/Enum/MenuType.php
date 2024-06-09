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

enum MenuType: string
{
    use EnumTrail;

    case MENU = 'menu'; // 菜单
    case BUTTON = 'button'; // 按钮
}
