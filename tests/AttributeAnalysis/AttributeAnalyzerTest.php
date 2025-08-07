<?php

declare(strict_types=1);

namespace AttributeAnalysis;

use Crell\AttributeUtils\ClassAnalyzer;
use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\AttributeAnalysis\AttributeAnalyzer;
use PhoneBurner\Pinch\Component\Tests\Fixtures\ClassWithAttributes;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AttributeAnalyzerTest extends TestCase
{
    #[Test]
    public function hasReturnsTrueWhenAnalyzeReturnsObject(): void
    {
        $mockAnalyzer = $this->createMock(ClassAnalyzer::class);
        $mockAnalyzer->expects($this->once())
            ->method('analyze')
            ->willReturn(new \stdClass());

        $analyzer = new AttributeAnalyzer($mockAnalyzer);
        self::assertTrue($analyzer->has(ClassWithAttributes::class, Contract::class));
    }

    #[Test]
    public function hasReturnsFalseWhenAnalyzeThrowsException(): void
    {
        $mockAnalyzer = $this->createMock(ClassAnalyzer::class);
        $mockAnalyzer->expects($this->once())
            ->method('analyze')
            ->willThrowException(new \Exception('Analysis failed'));

        $analyzer = new AttributeAnalyzer($mockAnalyzer);
        self::assertFalse($analyzer->has(ClassWithAttributes::class, Contract::class));
    }

    #[Test]
    public function analyzeDelegatesToInnerAnalyzer(): void
    {
        $expected = new \stdClass();
        $mockAnalyzer = $this->createMock(ClassAnalyzer::class);
        $mockAnalyzer->expects($this->once())
            ->method('analyze')
            ->with(ClassWithAttributes::class, Contract::class, ['scope1'])
            ->willReturn($expected);

        $analyzer = new AttributeAnalyzer($mockAnalyzer);
        $result = $analyzer->analyze(ClassWithAttributes::class, Contract::class, ['scope1']);
        self::assertSame($expected, $result);
    }
}
