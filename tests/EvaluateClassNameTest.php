<?php

declare(strict_types=1);

namespace peripteraptos\WindstitchPHP\Tests;

use PHPUnit\Framework\TestCase;
use function peripteraptos\WindstitchPHP\evaluate_classname;

final class EvaluateClassNameTest extends TestCase
{
    public function testSimpleRecordVariants(): void
    {
        $props = ['variant' => 'primary'];
        $variants = [
            'variant' => [
                'primary' => 'bg-blue-600',
                'secondary' => 'bg-gray-200',
            ],
        ];

        $class = evaluate_classname(
            $props,
            $variants,
            [],
            [],
            'btn'
        );

        $this->assertSame('btn bg-blue-600', $class);
    }

    public function testDefaultVariantsAreUsedWhenPropMissing(): void
    {
        $props = []; // no variant or size passed
        $variants = [
            'variant' => [
                'primary' => 'bg-blue-600',
                'secondary' => 'bg-gray-200',
            ],
            'size' => [
                'sm' => 'text-xs',
                'md' => 'text-sm',
            ],
        ];

        $defaultVariants = [
            'variant' => 'secondary',
            'size' => 'md',
        ];

        $class = evaluate_classname(
            $props,
            $variants,
            $defaultVariants,
            [],
            'btn'
        );

        $this->assertSame('btn bg-gray-200 text-sm', $class);
    }

    public function testCallableVariant(): void
    {
        $props = ['padding' => 4];
        $variants = [
            'padding' => function ($value) {
                return 'p-' . $value;
            },
        ];

        $class = evaluate_classname(
            $props,
            $variants,
            [],
            [],
            'box'
        );

        $this->assertSame('box p-4', $class);
    }

    public function testCompoundVariantsApplyMostSpecific(): void
    {
        $props = [
            'variant' => 'primary',
            'size'    => 'lg',
        ];

        $variants = [
            'variant' => [
                'primary' => 'bg-blue-600',
                'secondary' => 'bg-gray-200',
            ],
            'size' => [
                'sm' => 'text-xs',
                'lg' => 'text-lg',
            ],
        ];

        $compoundVariants = [
            [
                'variant' => 'primary',
                'class' => 'shadow', // low precision (1 key)
            ],
            [
                'variant' => 'primary',
                'size' => 'lg',
                'class' => 'shadow-lg', // higher precision (2 keys)
            ],
        ];

        $class = evaluate_classname(
            $props,
            $variants,
            [],
            $compoundVariants,
            'btn'
        );

        // should pick the more precise compound: shadow-lg
        $this->assertSame(
            'btn bg-blue-600 text-lg shadow-lg',
            $class
        );
    }

    public function testCompoundVariantDefaultToOverridesDefaults(): void
    {
        $props = [
            'variant' => 'special',
        ];

        $variants = [
            'variant' => [
                'default' => 'bg-gray-200',
                'special' => 'bg-purple-600',
            ],
            'size' => [
                'sm' => 'text-xs',
                'md' => 'text-sm',
                'lg' => 'text-lg',
            ],
        ];

        $defaultVariants = [
            'variant' => 'default',
            'size' => 'md',
        ];

        $compoundVariants = [
            [
                'variant' => 'special',
                'class' => 'ring-2',
                'defaultTo' => [
                    'size' => 'lg',
                ],
            ],
        ];

        $class = evaluate_classname(
            $props,
            $variants,
            $defaultVariants,
            $compoundVariants,
            'btn'
        );

        // variant provided in props, size defaulted to 'lg' via defaultTo
        $this->assertSame(
            'btn bg-purple-600 text-lg ring-2',
            $class
        );
    }
}
