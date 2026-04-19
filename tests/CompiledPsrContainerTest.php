<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\CompileInjector;
use Ray\Compiler\Compiler;

use const DIRECTORY_SEPARATOR;

final class CompiledPsrContainerTest extends TestCase
{
    private const COMPILE_INJECTOR_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'compile-injector';
    private const COMPILED_INJECTOR_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'compiled-injector';

    public static function setUpBeforeClass(): void
    {
        (new ScriptDir())->init(self::COMPILE_INJECTOR_DIR);
        (new ScriptDir())->init(self::COMPILED_INJECTOR_DIR);

        parent::setUpBeforeClass();
    }

    /**
     * CompileInjector is deprecated and does not support PHP 8.5
     */
    #[Test]
    #[RequiresPhp('< 8.5.0')]
    public function getWithCompileInjector(): void
    {
        $injector = new CompileInjector(self::COMPILE_INJECTOR_DIR, new FakeLazyModule());
        $container = new PsrContainer($injector);
        $actual = $container->get(FakeRobotInterface::class);

        self::assertInstanceOf(FakeRobotInterface::class, $actual);
        self::assertInstanceOf(FakeRobot::class, $actual);
    }

    #[Test]
    public function getWithCompiledInjector(): void
    {
        (new Compiler())->compile(new FakeModule(), self::COMPILED_INJECTOR_DIR);
        $injector = new CompiledInjector(self::COMPILED_INJECTOR_DIR);
        $container = new PsrContainer($injector);
        $actual = $container->get(FakeRobotInterface::class);

        self::assertInstanceOf(FakeRobotInterface::class, $actual);
        self::assertInstanceOf(FakeRobot::class, $actual);
    }
}
