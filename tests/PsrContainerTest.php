<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use NaokiTsuchiya\RayDiPsrContainer\Attribute\Left;
use NaokiTsuchiya\RayDiPsrContainer\Exception\ContainerException;
use NaokiTsuchiya\RayDiPsrContainer\Exception\InvalidIdException;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\CompileInjector;
use Ray\Compiler\Compiler;
use Ray\Di\Injector;

use const DIRECTORY_SEPARATOR;

final class PsrContainerTest extends TestCase
{
    private const TMP_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'compile';

    private PsrContainer $injector;

    public static function setUpBeforeClass(): void
    {
        (new ScriptDir())->init(self::TMP_DIR);

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        $injector = new Injector(new FakeModule());
        $this->injector = new PsrContainer($injector);

        parent::setUp();
    }

    #[Test]
    public function get(): void
    {
        $actual = $this->injector->get(FakeRobotInterface::class);

        self::assertInstanceOf(FakeRobotInterface::class, $actual);
        self::assertInstanceOf(FakeRobot::class, $actual);
    }

    #[Test]
    public function getWithInterfaceAndName(): void
    {
        $actual = $this->injector->get(
            IdentityStringGenerator::generate(
                FakeLegInterface::class,
                Left::class,
            ),
        );

        self::assertInstanceOf(FakeLegInterface::class, $actual);
        self::assertInstanceOf(FakeLeg::class, $actual);
    }

    #[Test]
    public function getWithOnlyName(): void
    {
        $actual = $this->injector->get(
            IdentityStringGenerator::generate('', 'name'),
        );

        self::assertSame('instance', $actual);
    }

    #[Test]
    public function getWithUnbound(): void
    {
        self::expectException(NotFoundExceptionInterface::class);

        $this->injector->get(FakeUnboundInterface::class);
    }

    #[Test]
    public function getWithUnknownInterface(): void
    {
        self::expectException(InvalidIdException::class);
        self::expectExceptionMessage('"invalid" is not a class name or interface name');

        $this->injector->get('invalid');
    }

    #[Test]
    public function getWithEmptyString(): void
    {
        self::expectException(InvalidIdException::class);
        self::expectExceptionMessage('id must not be empty');

        $this->injector->get('');
    }

    #[Test]
    public function getWithSeparationChar(): void
    {
        self::expectException(InvalidIdException::class);
        self::expectExceptionMessage('id must not be only a separator.');

        $this->injector->get(IdentityParser::NAME_SEPARATOR);
    }

    #[Test]
    public function getWithExceptionThrowInjector(): void
    {
        self::expectException(ContainerException::class);

        (new PsrContainer(new FakeExceptionThrowInjector()))->get(FakeRobotInterface::class);
    }

    #[Test]
    #[RequiresPhp('< 8.5')]
    public function getWithCompileInjector(): void
    {
        $injector = new CompileInjector(self::TMP_DIR, new FakeLazyModule());
        $container = new PsrContainer($injector);
        $actual = $container->get(FakeRobotInterface::class);

        self::assertInstanceOf(FakeRobotInterface::class, $actual);
        self::assertInstanceOf(FakeRobot::class, $actual);
    }

    #[Test]
    public function getWithCompiledInjector(): void
    {
        (new Compiler())->compile(new FakeModule(), self::TMP_DIR);
        $injector = new CompiledInjector(self::TMP_DIR);
        $container = new PsrContainer($injector);
        $actual = $container->get(FakeRobotInterface::class);

        self::assertInstanceOf(FakeRobotInterface::class, $actual);
        self::assertInstanceOf(FakeRobot::class, $actual);
    }

    #[Test]
    public function hasIsTrueWhenFakeRobotInterface(): void
    {
        self::assertTrue($this->injector->has(FakeRobotInterface::class));
    }

    #[Test]
    public function hasIsFalseWhenInvalid(): void
    {
        self::assertFalse($this->injector->has(FakeUnboundInterface::class));
    }
}
