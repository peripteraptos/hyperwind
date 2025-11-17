<?php

namespace peripteraptos\Hyperwind;

use function peripteraptos\Hyperwind\evaluate_classname;

const VOID_TAGS = [
    'area',
    'base',
    'br',
    'col',
    'embed',
    'hr',
    'img',
    'input',
    'link',
    'meta',
    'param',
    'source',
    'track',
    'wbr'
];

/**
 * styled('button', [
 *   'className' => 'base classes',
 *   'variants' => [...],
 *   'defaultVariants' => [...],
 *   'compoundVariants' => [...],
 *   'defaultProps' => ['type' => 'button']
 * ])
 */
function styled(string $defaultAs, array $config = []): callable
{
    $variants = $config['variants'] ?? [];
    $defaultVariants = $config['defaultVariants'] ?? [];
    $compoundVariants = $config['compoundVariants'] ?? [];
    $defaultProps = $config['defaultProps'] ?? [];
    $defaultClassName = $config['className'] ?? '';

    // This closure is your "component"
    return function (array $props = []) use (
        $defaultAs,
        $variants,
        $defaultVariants,
        $compoundVariants,
        $defaultProps,
        $defaultClassName
    ): string {
        $tag = $props['as'] ?? $defaultAs;
        unset($props['as']);

        // Merge defaultProps & user props
        $attrs = array_merge($defaultProps, $props);

        // Extract children before we strip stuff
        $children = $attrs['children'] ?? '';
        unset($attrs['children']);

        // Remove variant props from HTML attributes
        foreach (array_keys($variants) as $variantKey) {
            unset($attrs[$variantKey]);
        }

        // Compute className from *original* props (which include variants)
        $className = evaluate_classname(
            $props,
            $variants,
            $defaultVariants,
            $compoundVariants,
            $defaultClassName
        );

        // Merge into 'class'
        if ($className !== '') {
            $attrs['class'] = $className;
        }

        // Build HTML attributes
        $attrString = '';
        foreach ($attrs as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            if ($value === true) {
                $attrString .= ' ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            } else {
                $attrString .= ' ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') .
                    '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        $tagLower = strtolower($tag);

        if (in_array($tagLower, VOID_TAGS, true)) {
            // <img ...>
            return "<{$tag}{$attrString}>";
        }

        return "<{$tag}{$attrString}>{$children}</{$tag}>";
    };
}
