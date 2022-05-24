<?php

declare(strict_types=1);

namespace BeSimple\SoapCommon\PropertyAccess;

use Closure;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Symfony property access cannot access a private property or method, therefore this `nelmo/alice` inspired
 * Reflection based PropertyAccessor has been added, see links below for more information.
 *
 * @link https://github.com/symfony/symfony/issues/23938
 * @link https://github.com/symfony/symfony/issues/23938#issuecomment-325186998
 * @link https://github.com/nelmio/alice/blob/master/src/PropertyAccess/ReflectionPropertyAccessor.php
 */
final class ReflectionPropertyAccessor implements PropertyAccessorInterface
{
    private PropertyAccessorInterface $decoratedPropertyAccessor;

    public function __construct(PropertyAccessorInterface $decoratedPropertyAccessor)
    {
        $this->decoratedPropertyAccessor = $decoratedPropertyAccessor;
    }

    /**
     * @inheritDoc
     */
    public function setValue(&$objectOrArray, $propertyPath, $value): void
    {
        try {
            $this->decoratedPropertyAccessor->setValue($objectOrArray, $propertyPath, $value);
        } catch (NoSuchPropertyException $e) {
            $propertyReflectionProperty = $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
            if (null === $propertyReflectionProperty) {
                throw $e;
            }

            if ($propertyReflectionProperty->getDeclaringClass()->getName() !== get_class($objectOrArray)) {
                $propertyReflectionProperty->setAccessible(true);

                $propertyReflectionProperty->setValue($objectOrArray, $value);

                return;
            }

            $setPropertyClosure = Closure::bind(
                fn($object) => $object->{$propertyPath} = $value,
                $objectOrArray,
                $objectOrArray
            );

            $setPropertyClosure($objectOrArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function getValue($objectOrArray, $propertyPath)
    {
        try {
            return $this->decoratedPropertyAccessor->getValue($objectOrArray, $propertyPath);
        } catch (NoSuchPropertyException $e) {
            $propertyReflectionProperty = $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
            if (null === $propertyReflectionProperty) {
                throw $e;
            }

            if ($propertyReflectionProperty->getDeclaringClass()->getName() !== get_class($objectOrArray)) {
                $propertyReflectionProperty->setAccessible(true);

                return $propertyReflectionProperty->getValue($objectOrArray);
            }

            $getPropertyClosure = Closure::bind(
                fn($object) => $object->{$propertyPath},
                $objectOrArray,
                $objectOrArray
            );

            return $getPropertyClosure($objectOrArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function isWritable($objectOrArray, $propertyPath): bool
    {
        return (
            $this->decoratedPropertyAccessor->isWritable($objectOrArray, $propertyPath) ||
            $this->propertyExists($objectOrArray, $propertyPath)
        );
    }

    /**
     * @inheritDoc
     */
    public function isReadable($objectOrArray, $propertyPath): bool
    {
        return (
            $this->decoratedPropertyAccessor->isReadable($objectOrArray, $propertyPath) ||
            $this->propertyExists($objectOrArray, $propertyPath)
        );
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
    ): ?ReflectionProperty {
        if (false === is_object($objectOrArray)) {
            return null;
        }

        $reflectionClass = (new ReflectionClass(get_class($objectOrArray)));

        while ($reflectionClass instanceof ReflectionClass) {
            if ($reflectionClass->hasProperty($propertyPath) &&
                false === $reflectionClass->getProperty($propertyPath)->isStatic()
            ) {
                return $reflectionClass->getProperty($propertyPath);
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        return null;
    }
}
