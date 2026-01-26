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
    private const TMP_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'compile';

    public static function setUpBeforeClass(): void
    {
        (new ScriptDir())->init(self::TMP_DIR);

        parent::setUpBeforeClass();
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
}
