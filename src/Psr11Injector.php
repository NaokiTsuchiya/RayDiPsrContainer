<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use NaokiTsuchiya\RayDiPsrContainer\Exception\ContainerException;
use NaokiTsuchiya\RayDiPsrContainer\Exception\Unbound;
use Psr\Container\ContainerInterface;
use Ray\Di\Exception\Unbound as RayDiUnbound;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;
use Throwable;

final class Psr11Injector implements ContainerInterface, InjectorInterface
{
    private IdentityParser $identityParser;

    public function __construct(private InjectorInterface $injector)
    {
        $this->identityParser = new IdentityParser();
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
        $parsedId = $this->identityParser->parse($id);

        try {
            $instance = $this->getInstance(...$parsedId);
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
            $this->getInstance(...$this->identityParser->parse($id));
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
