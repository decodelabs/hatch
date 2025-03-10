# Hatch

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/hatch?style=flat)](https://packagist.org/packages/decodelabs/hatch)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/hatch.svg?style=flat)](https://packagist.org/packages/decodelabs/hatch)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/hatch.svg?style=flat)](https://packagist.org/packages/decodelabs/hatch)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/hatch/integrate.yml?branch=develop)](https://github.com/decodelabs/hatch/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/hatch?style=flat)](https://packagist.org/packages/decodelabs/hatch)

### Simple code generation tools

Hatch provides a set of simple tools for generating PHP code.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---

## Installation

Install via Composer:

```bash
composer require decodelabs/hatch
```

## Usage

### Static arrays

Export arrays of data for reuse as config files or static loadable datastructures.

```php
use DecodeLabs\Hatch;

$code = Hatch::exportStaticArray([
    'foo' => 'bar',
    'baz' => 'qux',
    'quux' => [
        'corge' => 'grault',
        'garply' => 12,
        'fred' => [
            'plugh' => 'xyzzy',
            'thud' => [1, 2, 3]
        ]
    ]
]);

echo $code;
```

This will output:

```php
[
    'foo' => 'bar',
    'baz' => 'qux',
    'quux' => [
        'corge' => 'grault',
        'garply' => 12,
        'fred' => [
            'plugh' => 'xyzzy',
            'thud' => [1, 2, 3]
        ]
    ]
]
```

## Licensing

Hatch is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
