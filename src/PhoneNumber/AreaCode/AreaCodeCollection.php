<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber\AreaCode;

use Countable;
use Generator;
use IteratorAggregate;
use PhoneBurner\Pinch\Array\Arrayable;
use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCodeAware;

/**
 * @implements IteratorAggregate<AreaCode>
 * @implements Arrayable<int<200,999>, AreaCode>
 */
#[Contract]
final readonly class AreaCodeCollection implements
    Arrayable,
    IteratorAggregate,
    Countable
{
    /**
     * @var array<int<200,999>, AreaCode>
     */
    private array $area_codes;

    public function __construct(AreaCodeAware ...$values)
    {
        $area_codes = [];
        foreach ($values as $area_code) {
            $area_code = $area_code->getAreaCode();
            $area_codes[$area_code->npa] = $area_code;
        }
        $this->area_codes = $area_codes;
    }

    public function contains(AreaCodeAware $area_code): bool
    {
        return isset($this->area_codes[$area_code->getAreaCode()->npa]);
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->area_codes);
    }

    public function filter(callable $callable): self
    {
        return new self(...\array_filter($this->area_codes, $callable));
    }

    /**
     * @return array<int<200,999>, AreaCode>
     */
    #[\Override]
    public function toArray(): array
    {
        return $this->area_codes;
    }

    /**
     * @return Generator<AreaCode>
     */
    #[\Override]
    public function getIterator(): Generator
    {
        yield from $this->area_codes;
    }
}
