<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use NaokiTsuchiya\RayDiPsrContainer\Exception\InvalidIdException;
use Ray\Di\Name;

use function class_exists;
use function interface_exists;
use function sprintf;
use function strpos;
use function substr;

class IdentityParser
{
    public const NAME_SEPARATOR = '#';
    private const ERROR_ID_EMPTY = 'id must not be empty.';
    private const ERROR_ID_ONLY_SEPARATOR = 'id must not be only a separator.';
    private const ERROR_INVALID_INTERFACE = '"%s" is not a class name or interface name';

    /**
     * Parse the given ID string into an interface and a name.
     *
     * @return array{interface: class-string|'', name: string}
     *
     * @throws InvalidIdException
     */
    public function parse(string $id): array
    {
        if ($id === '') {
            throw new InvalidIdException(self::ERROR_ID_EMPTY);
        }

        if ($id === self::NAME_SEPARATOR) {
            throw new InvalidIdException(self::ERROR_ID_ONLY_SEPARATOR);
        }

        $separatorPosition = strpos($id, self::NAME_SEPARATOR);

        if ($separatorPosition === false) {
            // The ID has no separator, implying only an interface is provided.
            return $this->validateInterfaceAndReturn($id, Name::ANY);
        }

        if ($separatorPosition === 0) {
            // The ID starts with a separator, implying only a name is provided (e.g., #name).
            return ['interface' => '', 'name' => substr($id, 1)];
        }

        /** @psalm-var non-empty-string $interface */ // False positive for ArgumentTypeCoercion
        $interface = substr($id, 0, $separatorPosition);
        $name = substr($id, $separatorPosition + 1);

        return $this->validateInterfaceAndReturn($interface, $name);
    }

    /**
     * Validate the given interface and return it with the name.
     *
     * @param non-empty-string $interface
     *
     * @return array{interface: class-string, name: string}
     *
     * @throws InvalidIdException
     */
    private function validateInterfaceAndReturn(string $interface, string $name): array
    {
        if ($this->isValidInterfaceOrClassName($interface)) {
            return ['interface' => $interface, 'name' => $name];
        }

        throw new InvalidIdException(sprintf(self::ERROR_INVALID_INTERFACE, $interface));
    }

    /**
     * Check if the string is a valid class or interface name.
     *
     * @param non-empty-string $interface
     *
     * @phpstan-assert-if-true class-string $interface
     */
    private function isValidInterfaceOrClassName(string $interface): bool
    {
        return class_exists($interface) || interface_exists($interface);
    }
}
