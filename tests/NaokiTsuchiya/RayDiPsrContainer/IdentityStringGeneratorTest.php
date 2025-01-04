<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use Generator;
use PHPUnit\Framework\TestCase;

final class IdentityStringGeneratorTest extends TestCase
{
    /**
     * @param array{interface: string, name?: string} $params
     *
     * @test
     * @dataProvider paramDataProvider
     */
    public function generate(array $params, string $expected): void
    {
        $actual = IdentityStringGenerator::generate(...$params);

        self::assertSame($expected, $actual);
    }

    public function paramDataProvider(): Generator
    {
        yield 'only class-string interface' => [
            'params' => [
                'interface' => FakeRobotInterface::class,
            ],
            'expected' => FakeRobotInterface::class . IdentityParser::NAME_SEPARATOR,
        ];

        yield 'class-string interface and name' => [
            'params' => [
                'interface' => FakeRobotInterface::class,
                'name' => 'foo',
            ],
            'expected' => FakeRobotInterface::class . IdentityParser::NAME_SEPARATOR . 'foo',
        ];

        yield 'empty string interface and name' => [
            'params' => [
                'interface' => '',
                'name' => 'foo',
            ],
            'expected' => IdentityParser::NAME_SEPARATOR . 'foo',
        ];

        yield 'empty' => [
            'params' => [
                'interface' => '',
                'name' => '',
            ],
            'expected' => '#',
        ];
    }
}
