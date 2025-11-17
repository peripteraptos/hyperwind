<?php

declare(strict_types=1);

namespace Hyperwind\Tests;

use PHPUnit\Framework\TestCase;
use function Hyperwind\styled;

final class StyledTest extends TestCase
{
    public function testStyledRendersBaseTagWithClassesAndChildren(): void
    {
        $Button = styled('button', [
            'className' => 'inline-flex',
            'defaultProps' => [
                'type' => 'button',
            ],
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
        ]);

        $html = $Button([
            'variant'  => 'secondary',
            'size'     => 'sm',
            'children' => 'Click me',
            'id'       => 'save-button',
        ]);

        $this->assertStringContainsString('<button', $html);
        $this->assertStringContainsString('id="save-button"', $html);
        $this->assertStringContainsString('type="button"', $html);
        $this->assertStringContainsString('Click me', $html);

        // classes composed from base + variants
        $this->assertStringContainsString('inline-flex', $html);
        $this->assertStringContainsString('bg-gray-100 text-gray-900', $html);
        $this->assertStringContainsString('text-xs px-2 py-1', $html);

        // variant props must NOT be rendered as attributes
        $this->assertStringNotContainsString('variant="', $html);
        $this->assertStringNotContainsString('size="', $html);
    }

    public function testStyledMergesExistingClassAttribute(): void
    {
        $Box = styled('div', [
            'className' => 'base',
            'variants' => [
                'padded' => [
                    true => 'p-4',
                    false => '',
                ],
            ],
            'defaultVariants' => [
                'padded' => true,
            ],
        ]);

        $html = $Box([
            'class' => 'custom',
            'children' => 'Hello',
        ]);

        // class attribute should contain both
        $this->assertStringContainsString('class="custom base p-4"', $html);
    }

    public function testStyledSupportsAsPropToChangeTag(): void
    {
        $Button = styled('button', [
            'className' => 'inline-flex',
            'variants' => [
                'variant' => [
                    'primary' => 'bg-blue-600',
                    'link' => 'text-blue-600 underline',
                ],
            ],
            'defaultVariants' => [
                'variant' => 'primary',
            ],
        ]);

        $html = $Button([
            'as'       => 'a',
            'href'     => '/foo',
            'variant'  => 'link',
            'children' => 'Go',
        ]);

        $this->assertStringStartsWith('<a ', $html);
        $this->assertStringContainsString('href="/foo"', $html);
        $this->assertStringContainsString('text-blue-600 underline', $html);
        $this->assertStringNotContainsString('variant="', $html);
    }

    public function testVoidTagsDoNotRenderClosingTagOrChildren(): void
    {
        $Img = styled('img', [
            'className' => 'rounded',
            'variants' => [
                'size' => [
                    'thumb' => 'w-16 h-16',
                    'full' => 'w-full',
                ],
            ],
            'defaultVariants' => [
                'size' => 'thumb',
            ],
        ]);

        $html = $Img([
            'src' => '/image.jpg',
            'alt' => 'Test',
            'children' => 'IGNORED',
        ]);

        $this->assertStringStartsWith('<img ', $html);
        $this->assertStringContainsString('src="/image.jpg"', $html);
        $this->assertStringContainsString('alt="Test"', $html);
        $this->assertStringContainsString('rounded w-16 h-16', $html);

        // no closing tag, no children text
        $this->assertStringNotContainsString('</img>', $html);
        $this->assertStringNotContainsString('IGNORED', $html);
    }
}
