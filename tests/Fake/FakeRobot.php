<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use NaokiTsuchiya\RayDiPsrContainer\Attribute\Left;
use NaokiTsuchiya\RayDiPsrContainer\Attribute\Right;

class FakeRobot implements FakeRobotInterface
{
    public function __construct(
        #[Left]
        public FakeLegInterface $leftLeg,
        #[Right]
        public FakeLegInterface $rightLeg,
    )
    {
    }
}
