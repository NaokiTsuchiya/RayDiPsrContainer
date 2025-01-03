<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use NaokiTsuchiya\RayDiPsrContainer\Attribute\Left;
use NaokiTsuchiya\RayDiPsrContainer\Attribute\Right;
use Ray\Di\AbstractModule;

class FakeModule extends AbstractModule
{

    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->to(FakeRobot::class);
        $this->bind(FakeLegInterface::class)->annotatedWith(Left::class)->to(FakeLeg::class);
        $this->bind(FakeLegInterface::class)->annotatedWith(Right::class)->to(FakeLeg::class);
        $this->bind()->annotatedWith('name')->toInstance('instance');
    }
}
