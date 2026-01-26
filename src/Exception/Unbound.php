<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class Unbound extends \Ray\Di\Exception\Unbound implements NotFoundExceptionInterface, ExceptionInterface
{
}
