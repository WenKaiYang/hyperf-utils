<?php

namespace Ella123\HyperfUtils;

use Hyperf\Collection\Collection;

class Arr extends \Hyperf\Collection\Arr
{
    public static function toTree(
        array $rows,
        string $pidColumn = 'pid',
        string $idColumn = 'id',
        string $childrenColumn = 'children'
    ): array {
        $tree = [];
        $temp = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $temp[$row[$idColumn]] = $row;
        }
        foreach ($temp as $data) {
            if (isset($temp[$data[$pidColumn]]) && $data[$pidColumn]) {
                $temp[$data[$pidColumn]][$childrenColumn][] = &$temp[$data[$idColumn]];
            } else {
                $tree[] = &$temp[$data[$idColumn]];
            }
        }

        return $tree;
    }

    public static function toGroupOptions(
        array|Collection $groups,
        ?string $label = 'title',
        ?string $value = 'id'
    ): array {
        $items = [];

        foreach ($groups as $group => $rows) {
            $items[] = [
                'label' => $group,
                'options' => self::toOptions($rows, $label, $value),
            ];
        }

        return $items;
    }

    public static function toOptions(
        array|Collection $rows,
        ?string $label = 'title',
        ?string $value = 'id'
    ): array {
        $items = [];

        foreach ($rows as $key => $row) {
            if ($label && $value && isset($row[$label], $row[$value])) {
                $items[] = [
                    'value' => (string) $row[$value],
                    'label' => (string) $row[$label],
                    'disabled' => (bool) ($row['disabled'] ?? ! ($row['enable'] ?? true)),
                ];
            } else {
                $items[] = [
                    'value' => (string) $key,
                    'label' => (string) $row,
                ];
            }
        }

        return $items;
    }

    public static function treeToOptions(
        array $tree,
        string $label = 'title',
        string $value = 'id',
        string $children = 'children'
    ): array {
        $items = [];

        foreach ($tree as $item) {
            if (isset($item[$children])) {
                $items[] = [
                    'value' => (string) $item[$value],
                    'label' => (string) $item[$label],
                    'isLeaf' => false,
                    'hasChildren' => true,
                    'disabled' => (bool) ($item['disabled'] ?? ! ($item['enable'] ?? true)),
                    'children' => self::treeToOptions($item[$children], $label, $value, $children),
                ];
            } else {
                $items[] = [
                    'value' => (string) $item[$value],
                    'label' => (string) $item[$label],
                    'isLeaf' => true,
                    'hasChildren' => false,
                    'disabled' => (bool) ($item['disabled'] ?? ! ($item['enable'] ?? true)),
                ];
            }
        }

        return $items;
    }

}