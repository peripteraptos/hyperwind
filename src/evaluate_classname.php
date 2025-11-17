<?php

namespace peripteraptos\Hyperwind;

function evaluate_classname(
    array $props,
    array $variants,
    array $defaultVariants = [],
    array $compoundVariants = [],
    string $defaultClassName = ''
): string {
    $propsClass = $props['class'] ?? ($props['className'] ?? '');
    $classNames = [$propsClass, $defaultClassName];

    $compoundedClassName = '';
    $compoundedDefaults = [];

    // helper to get a variant value from props/defaults/compoundDefaults
    $getVariantValue = function (string $key, bool $selectFromCompounded = false) use (
        $props,
        $defaultVariants,
        &$compoundedDefaults
    ) {
        if (!array_key_exists($key, $props) || $props[$key] === null) {
            $defaultValue = $defaultVariants[$key] ?? null;
            if ($selectFromCompounded) {
                return $compoundedDefaults[$key] ?? $defaultValue;
            }
            return $defaultValue;
        }
        return $props[$key];
    };

    // handle compoundVariants
    if (!empty($compoundVariants)) {
        $lastPrecision = 0;

        foreach ($compoundVariants as $cv) {
            $selector = $cv;
            $class = $selector['class'] ?? '';
            $defaultTo = $selector['defaultTo'] ?? [];
            unset($selector['class'], $selector['defaultTo']);

            $keys = array_keys($selector);
            $precision = count($keys);

            $matches = true;
            foreach ($keys as $key) {
                if ($getVariantValue($key, false) !== $selector[$key]) {
                    $matches = false;
                    break;
                }
            }

            if ($matches && $precision >= $lastPrecision) {
                $compoundedClassName = $class ?: '';
                $compoundedDefaults = $defaultTo ?: [];
                $lastPrecision = $precision;
            }
        }
    }

    // add variant classes
    foreach ($variants as $key => $variant) {
        $value = $getVariantValue($key, true);

        if (is_callable($variant)) {
            $class = $variant($value, $props, $variants);
            if (is_string($class) && trim($class) !== '') {
                $classNames[] = trim($class);
            }
        } elseif (is_array($variant)) {
            if (isset($variant[$value]) && trim($variant[$value]) !== '') {
                $classNames[] = trim($variant[$value]);
            }
        }
    }

    if ($compoundedClassName) {
        $classNames[] = $compoundedClassName;
    }

    // filter empty and join
    $classNames = array_filter($classNames, fn($c) => is_string($c) && trim($c) !== '');
    return implode(' ', $classNames);
}
