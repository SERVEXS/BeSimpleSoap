<?php

declare(strict_types=1);

namespace BeSimple\SoapCommon\PropertyAccess;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Symfony property access cannot access a private property or method, therefore this `nelmo/alice` inspired
 * Reflection based PropertyAccessor has been added, see links below for more information.
 *
 * @see https://github.com/symfony/symfony/issues/23938
 * @see https://github.com/symfony/symfony/issues/23938#issuecomment-325186998
 * @see https://github.com/nelmio/alice/blob/master/src/PropertyAccess/ReflectionPropertyAccessor.php
 */
final readonly class ReflectionPropertyAccessor implements PropertyAccessorInterface
{
    public function __construct(private PropertyAccessorInterface $decoratedPropertyAccessor)
    {
    }

    public function setValue(object|array &$objectOrArray, string|PropertyPathInterface $propertyPath, mixed $value): void
    {
        try {
            $this->decoratedPropertyAccessor->setValue($objectOrArray, $propertyPath, $value);
        } catch (NoSuchPropertyException $e) {
            $propertyReflectionProperty = $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
            if (null === $propertyReflectionProperty) {
                throw $e;
            }

            if ($propertyReflectionProperty->getDeclaringClass()->getName() !== $objectOrArray::class) {
                $propertyReflectionProperty->setAccessible(true);

                $propertyReflectionProperty->setValue($objectOrArray, $value);

                return;
            }

            $setPropertyClosure = \Closure::bind(
                fn ($object) => $object->{$propertyPath} = $value,
                $objectOrArray,
                $objectOrArray
            );

            $setPropertyClosure($objectOrArray);
        }
    }

    public function getValue(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): mixed
    {
        try {
            return $this->decoratedPropertyAccessor->getValue($objectOrArray, $propertyPath);
        } catch (NoSuchPropertyException $e) {
            $propertyReflectionProperty = $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
            if (null === $propertyReflectionProperty) {
                throw $e;
            }

            if ($propertyReflectionProperty->getDeclaringClass()->getName() !== $objectOrArray::class) {
                $propertyReflectionProperty->setAccessible(true);

                return $propertyReflectionProperty->getValue($objectOrArray);
            }

            $getPropertyClosure = \Closure::bind(
                fn ($object) => $object->{$propertyPath},
                $objectOrArray,
                $objectOrArray
            );

            return $getPropertyClosure($objectOrArray);
        }
    }

    public function isWritable(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        return
            $this->decoratedPropertyAccessor->isWritable($objectOrArray, $propertyPath)
            || $this->propertyExists($objectOrArray, $propertyPath)
        ;
    }

    public function isReadable($objectOrArray, $propertyPath): bool
    {
        return
            $this->decoratedPropertyAccessor->isReadable($objectOrArray, $propertyPath)
            || $this->propertyExists($objectOrArray, $propertyPath)
        ;
    }

    /**
     * @param object|array $objectOrArray
     * @param string|PropertyPathInterface $propertyPath
     */
    private function propertyExists($objectOrArray, string $propertyPath): bool
    {
        return null !== $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
    }

    /**
     * @param object|array $objectOrArray
     * @param string|PropertyPathInterface $propertyPath
     */
    private function getPropertyReflectionProperty(
        $objectOrArray,
        $propertyPath
    ): ?\ReflectionProperty {
        if (false === \is_object($objectOrArray)) {
            return null;
        }

        $reflectionClass = (new \ReflectionClass($objectOrArray::class));

        while ($reflectionClass instanceof \ReflectionClass) {
            if ($reflectionClass->hasProperty($propertyPath)
                && false === $reflectionClass->getProperty($propertyPath)->isStatic()
            ) {
                return $reflectionClass->getProperty($propertyPath);
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        return null;
    }
}
