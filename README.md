# naoki-tsuchiya/ray-di-psr-container

`naoki-tsuchiya/ray-di-psr-container` is a package that implements the [PSR-11 (ContainerInterface)](https://www.php-fig.org/psr/psr-11/) and integrates seamlessly with [Ray.DI](https://ray-di.github.io/).
This library leverages the dependency injection capabilities of Ray.DI while providing a PSR-11 compatible interface.

## Installation

    composer require naoki-tsuchiya/ray-di-psr-container

## Usage

```php
<?php

use NaokiTsuchiya\RayDiPsrContainer\Psr11Injector;
use NaokiTsuchiya\RayDiPsrContainer\IdentityStringGenerator;
use Ray\Di\Injector;

require_once 'vendor/autoload.php';

// Create an Injector
$injector = new Injector(new FooModule());

// Create a Psr11Injector
$container = new Psr11Injector($injector);

// Retrieve an instance
$instance = $container->get(FooInterface::class);

// Check if a binding exists
$isAvailable = $container->has(FooInterface::class);

// Using get() method with IdentityStringGenerator
// Compatible with Injector's getInstance method.
$namedInstance = $container->get(IdentityStringGenerator::generate(Foo::class, NAME::class));
```

## Development

### Install

    composer install

### Available Commands

    composer test              // Run unit test
    composer tests             // Test and quality checks
    composer cs                // Run coding style check
    composer cs-fix            // Fix the coding style
    composer sa                // Run static analysis tools
    composer run-script --list // List all available commands
