<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use Ray\Compiler\LazyModuleInterface;
use Ray\Di\AbstractModule;

class FakeLazyModule implements LazyModuleInterface
{
    public function __invoke(): AbstractModule
    {
        return new FakeModule();
    }
}
