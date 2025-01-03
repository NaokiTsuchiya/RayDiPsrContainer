<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use NaokiTsuchiya\RayDiPsrContainer\Exception\ContainerException;
use NaokiTsuchiya\RayDiPsrContainer\Exception\InvalidIdException;
use NaokiTsuchiya\RayDiPsrContainer\Exception\Unbound;
use Psr\Container\ContainerInterface;
use Ray\Di\Exception\Unbound as RayDiUnbound;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;
use Throwable;

use function class_exists;
use function interface_exists;
use function sprintf;
use function strpos;
use function substr;

class Psr11Injector implements ContainerInterface, InjectorInterface
{
    public const NAME_SEPARATOR = '#';

    public function __construct(private InjectorInterface $injector)
    {
    }

    /** {@inheritDoc} */
    public function getInstance($interface, $name = Name::ANY)
    {
        return $this->injector->getInstance($interface, $name);
    }

    /**
     * {@inheritDoc}
     *
     * Additionally, the `$id` parameter can include both an interface and a name, separated by `#`.
     * - For example, `Foo::class#name` will be parsed into the interface `Foo::class` and the name `name`.
     * - If `$id` starts with `#`, such as `#name`, the interface will be empty, and the name will be `name`.
     * - If `$id` does not include `#`, such as `Foo::class`, the interface will be `Foo::class` and the name will
     *   default to `Name::ANY`.
     */
    public function get(string $id)
    {
        [$interface, $name] = $this->parseId($id);

        try {
            $instance = $this->getInstance($interface, $name);
        } catch (RayDiUnbound $e) {
            throw new Unbound($e->getMessage(), 0, $e);
        } catch (Throwable $e) { // @codeCoverageIgnoreStart
            throw new ContainerException($e->getMessage(), 0, $e); // @codeCoverageIgnoreEnd
        }

        return $instance;
    }

    public function has(string $id): bool
    {
        try {
            [$interface, $name] = $this->parseId($id);
            $this->getInstance($interface, $name);
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    /** @return array{0: class-string|'', 1: string} */
    private function parseId(string $id): array
    {
        if ($id === '') {
            throw new InvalidIdException('id must not be empty.');
        }

        if ($id === '#') {
            throw new InvalidIdException('id must not be only a separator.');
        }

        $separatorPosition = strpos($id, self::NAME_SEPARATOR);

        if ($separatorPosition === false) {
            // Interface is provided without a name (e.g. Foo::class)
            return $this->handleInvalidInterface($id, Name::ANY);
        }

        if ($separatorPosition === 0) {
            // Only name is provided (e.g. #name)
            return ['', substr($id, 1)];
        }

        /** @psalm-var non-empty-string $interface */ // False positive for ArgumentTypeCoercion
        $interface = substr($id, 0, $separatorPosition);
        $name = substr($id, $separatorPosition + 1);

        return $this->handleInvalidInterface($interface, $name);
    }

    /**
     * @param non-empty-string $interface
     *
     * @return array{0: class-string, 1: string}
     */
    private function handleInvalidInterface(string $interface, string $name): array
    {
        if ($this->verifyInterfaceString($interface)) {
            return [$interface, $name];
        }

        throw new InvalidIdException(sprintf('"%s" is not a class name or interface name', $interface));
    }

    /**
     * @param non-empty-string $interface
     *
     * @phpstan-assert-if-true class-string $interface
     */
    private function verifyInterfaceString(string $interface): bool
    {
        return class_exists($interface) || interface_exists($interface);
    }
}
