<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Util;

/**
 * @template T of mixed
 */
class Collection implements \IteratorAggregate, \Countable
{
    /**
     * @var T[]
     */
    private array $elements = [];

    public function __construct(private readonly string $getter, private readonly ?string $class = null)
    {
    }

    /**
     * @param T $element
     */
    public function add($element): void
    {
        if ($this->class && !$element instanceof $this->class) {
            throw new \InvalidArgumentException(sprintf('Cannot add class "%s" because it is not an instance of "%s"', $element::class, $this->class));
        }

        $this->elements[$element->{$this->getter}()] = $element;
    }

    /**
     * @param T[] $elements
     */
    public function addAll($elements): void
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    public function has($key): bool
    {
        return isset($this->elements[$key]);
    }

    /**
     * @param string|int $key
     *
     * @return T|null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->elements[$key] : null;
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function count(): int
    {
        return \count($this->elements);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }
}
