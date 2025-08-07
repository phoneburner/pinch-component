<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\AttributeAnalysis;

use Crell\AttributeUtils\ClassAnalyzer;
use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
class AttributeAnalyzer implements ClassAnalyzer
{
    public function __construct(private readonly ClassAnalyzer $analyzer)
    {
    }

    /**
     * Analyzes a class or object for the specified attribute.
     *
     * @param class-string|object $class
     *   Either a fully qualified class name or an object to analyze.
     * @param class-string $attribute
     *   The fully qualified class name of the class attribute to analyze.
     * @param array<string|null> $scopes
     *   The scopes for which this analysis should run.
     */
    public function has(object|string $class, string $attribute, array $scopes = []): bool
    {
        try {
            return (bool)$this->analyze($class, $attribute, $scopes);
        } catch (\Exception) {
            return false;
        }
    }

    public function analyze(object|string $class, string $attribute, array $scopes = []): object
    {
        return $this->analyzer->analyze($class, $attribute, $scopes);
    }
}
