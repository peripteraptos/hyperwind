<?php

declare(strict_types=1);

namespace peripteraptos\Hyperwind\Tests;

use PHPUnit\Framework\TestCase;
use function peripteraptos\Hyperwind\wx;

final class WxTest extends TestCase
{
    public function testWxReturnsCallableThatBuildsClasses(): void
    {
        $buttonClass = wx([
            'className' => 'inline-flex',
            'variants' => [
                'variant' => [
                    'primary' => 'bg-blue-600 text-white',
                    'secondary' => 'bg-gray-100 text-gray-900',
                ],
                'size' => [
                    'sm' => 'text-xs px-2 py-1',
                    'md' => 'text-sm px-3 py-2',
                ],
            ],
            'defaultVariants' => [
                'variant' => 'primary',
                'size' => 'md',
            ],
            'compoundVariants' => [
                [
                    'variant' => 'primary',
                    'size' => 'md',
                    'class' => 'shadow',
                ],
            ],
        ]);

        $classes = $buttonClass([
            'variant' => 'secondary',
            'size' => 'sm',
        ]);

        $this->assertSame(
            'inline-flex bg-gray-100 text-gray-900 text-xs px-2 py-1',
            $classes
        );

        // with no props -> defaults + compound
        $classesDefault = $buttonClass([]);

        $this->assertSame(
            'inline-flex bg-blue-600 text-white text-sm px-3 py-2 shadow',
            $classesDefault
        );
    }
}
