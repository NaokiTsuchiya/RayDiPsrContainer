<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use Ray\Di\Name;

final class IdentityStringGenerator
{
    public static function generate(string $interface, string $name = Name::ANY): string
    {
        return $interface . IdentityParser::NAME_SEPARATOR . $name;
    }
}
