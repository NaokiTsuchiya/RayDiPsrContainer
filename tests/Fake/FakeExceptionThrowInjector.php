<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use Exception;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;

final class FakeExceptionThrowInjector implements InjectorInterface
{
    /** {@inheritDoc} */
    public function getInstance($interface, $name = Name::ANY)
    {
        throw new Exception('This injector throws exception every time.');
    }
}
