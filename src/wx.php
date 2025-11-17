<?php

namespace peripteraptos\Hyperwind;

use function peripteraptos\Hyperwind\evaluate_classname;

/**
 * wx([
 *   'className' => 'base classes',
 *   'variants' => [...],
 *   'defaultVariants' => [...],
 *   'compoundVariants' => [...]
 * ])
 * returns function (array $props): string
 */
function wx(array $config): callable
{
    $variants = $config['variants'] ?? [];
    $defaultVariants = $config['defaultVariants'] ?? [];
    $compoundVariants = $config['compoundVariants'] ?? [];
    $className = $config['className'] ?? '';

    return function (array $props = []) use (
        $variants,
        $defaultVariants,
        $compoundVariants,
        $className
    ): string {
        return evaluate_classname(
            $props,
            $variants,
            $defaultVariants,
            $compoundVariants,
            $className
        );
    };
}
