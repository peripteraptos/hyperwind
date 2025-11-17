# Hyperwind

Hyperwind is a tiny PHP helper to build Tailwind (or any utility CSS) class strings using **variants** and **compound variants**, and to render small “styled components” directly as HTML strings.

It’s especially handy in server-side rendered PHP apps where you want a clean, declarative way to describe visual variants without scattering class strings everywhere.

> **Inspired by:**  
> This package is based on the ideas and API of the Typescript library **[Windstitch](https://github.com/vinpac/windstitch/tree/main)**. All credit for the original concept goes to that project.

## Installation

You can install the package via composer:

```bash
composer require peripteraptos/hyperwind
```

## API Overview

Hyperwind exposes three main functions:

- `wx(array $config): callable` – build a **class name factory**
- `styled(string $as, array $config = []): callable` – build a **small “styled component”** that renders HTML
- `evaluate_classname(...)` – internal helper used by both; you usually don’t need to call this directly

All functions live in the `Hyperwind` namespace.

## `wx` – class name builder

`wx` lets you define base classes, **variants**, **default variants**, and **compound variants**, and returns a callable that will produce the final class string.

```php
use function Hyperwind\wx;

$buttonClass = wx([
    'className' => 'inline-flex items-center justify-center rounded',
    'variants' => [
        'variant' => [
            'primary'   => 'bg-blue-600 text-white',
            'secondary' => 'bg-gray-100 text-gray-900',
        ],
        'size' => [
            'sm' => 'text-xs px-2 py-1',
            'md' => 'text-sm px-3 py-2',
        ],
    ],
    'defaultVariants' => [
        'variant' => 'primary',
        'size'    => 'md',
    ],
    'compoundVariants' => [
        [
            'variant' => 'primary',
            'size'    => 'md',
            'class'   => 'shadow',
        ],
    ],
]);

// With explicit props
$classes = $buttonClass([
    'variant' => 'secondary',
    'size'    => 'sm',
]);
// "inline-flex items-center justify-center rounded bg-gray-100 text-gray-900 text-xs px-2 py-1"

// With defaults (also applies compound variant)
$classesDefault = $buttonClass();
// "inline-flex items-center justify-center rounded bg-blue-600 text-white text-sm px-3 py-2 shadow"
```

**Config keys:**

- `className` – base classes, always included
- `variants` – array of variant name → map of value → class string, or a callable `fn($value, $props, $variants): string`
- `defaultVariants` – default value per variant when not passed in props
- `compoundVariants` – array of objects like:

  ```php
  [
      'variant' => 'primary',
      'size'    => 'lg',
      'class'   => 'shadow-lg',
      // optional: 'defaultTo' => ['size' => 'lg', ...]
  ]
  ```

  The most specific matching compound (with most keys) wins.

## `styled` – HTML “components” with variants

`styled` wraps `wx` and renders HTML elements with attributes. It returns a callable that you can use like a tiny component:

```php
use function Hyperwind\styled;

$Button = styled('button', [
    'className' => 'inline-flex items-center justify-center rounded',
    'variants' => [
        'variant' => [
            'primary'   => 'bg-blue-600 text-white',
            'secondary' => 'bg-gray-100 text-gray-900',
            'link'      => 'text-blue-600 underline bg-transparent',
        ],
        'size' => [
            'sm' => 'text-xs px-2 py-1',
            'md' => 'text-sm px-3 py-2',
        ],
    ],
    'defaultVariants' => [
        'variant' => 'primary',
        'size'    => 'md',
    ],
    'defaultProps' => [
        'type' => 'button',
    ],
]);

echo $Button([
    'variant'  => 'secondary',
    'size'     => 'sm',
    'id'       => 'save-button',
    'children' => 'Save',
]);
```

Output (simplified):

```html
<button
  id="save-button"
  type="button"
  class="inline-flex items-center justify-center rounded bg-gray-100 text-gray-900 text-xs px-2 py-1"
>
  Save
</button>
```

### Special props

- `children` – inner HTML/text of the element

- `as` – change the tag name:

  ```php
  echo $Button([
      'as'       => 'a',
      'href'     => '/profile',
      'variant'  => 'link',
      'children' => 'Profile',
  ]);
  // <a href="/profile" class="... text-blue-600 underline ...">Profile</a>
  ```

- Variant keys (e.g. `variant`, `size`) are **used only for class generation** and are **not rendered** as HTML attributes.

### Void tags

If you use a void tag like `img`, `input`, `br`, etc., `styled` will not render a closing tag or children:

```php
$Img = styled('img', [
    'className' => 'rounded',
    'variants' => [
        'size' => [
            'thumb' => 'w-16 h-16',
            'full'  => 'w-full',
        ],
    ],
    'defaultVariants' => [
        'size' => 'thumb',
    ],
]);

echo $Img([
    'src' => '/image.jpg',
    'alt' => 'Test',
    'children' => 'IGNORED', // ignored for void tags
]);
```

Gives:

```html
<img src="/image.jpg" alt="Test" class="rounded w-16 h-16" />
```

## `evaluate_classname` (low-level)

You usually don’t need this directly; it’s what powers `wx` and `styled`.

Signature:

```php
use function Hyperwind\evaluate_classname;

string evaluate_classname(
    array $props,
    array $variants,
    array $defaultVariants = [],
    array $compoundVariants = [],
    string $defaultClassName = ''
): string
```

It accepts the same structures as shown above and returns a composed class string.

## Testing

If you want to run the tests:

```bash
composer install
./vendor/bin/phpunit
```

## Credits

Hyperwind is **based on and heavily inspired by** the JavaScript library **[Windstitch](https://github.com/vinpac/windstitch/tree/main)**.
Concept, naming, and overall API design originate from that project – this package is a PHP adaptation.
