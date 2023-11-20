<?php

/*
 * This file is part of the BeSimpleSoap.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Loader;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\Alias;
use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType as ComplexTypeAnnotation;
use BeSimple\SoapBundle\ServiceDefinition\ComplexType;
use BeSimple\SoapBundle\ServiceDefinition\Definition;
use BeSimple\SoapBundle\Util\Collection;

/**
 * AnnotationComplexTypeLoader loads ServiceDefinition from a PHP class and its methods.
 *
 * Based on \Symfony\Component\Routing\Loader\AnnotationClassLoader
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class AnnotationComplexTypeLoader extends AnnotationClassLoader
{
    private string $aliasClass = Alias::class;

    private string $complexTypeClass = ComplexTypeAnnotation::class;

    /**
     * Loads a ServiceDefinition from annotations from a class.
     *
     * @param string $class A class name
     * @param string $type The resource type
     *
     * @return Definition A ServiceDefinition instance
     *
     * @throws \InvalidArgumentException When route can't be parsed
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $annotations = [];

        $class = new \ReflectionClass($class);
        if ($alias = $this->reader->getClassAnnotation($class, $this->aliasClass)) {
            $annotations['alias'] = $alias->getValue();
        }

        foreach ($class->getAttributes(Alias::class) as $alias) {
            $annotations['alias'] = $alias->getValue();
        }

        $annotations['properties'] = new Collection('getName', ComplexType::class);
        foreach ($class->getProperties() as $property) {
            $complexType = $this->reader->getPropertyAnnotation($property, $this->complexTypeClass);

            if ($complexType) {
                $propertyComplexType = new ComplexType();
                $propertyComplexType->setValue($complexType->getValue());
                $propertyComplexType->setNillable($complexType->isNillable());
                $propertyComplexType->setIsAttribute($complexType->isAttribute());
                $propertyComplexType->setName($property->getName());
                $annotations['properties']->add($propertyComplexType);
            }

            foreach ($property->getAttributes() as $attribute) {
                if (!$attribute instanceof ComplexTypeAnnotation) {
                    continue;
                }

                $propertyComplexType = new ComplexType();
                $propertyComplexType->setValue($attribute->getValue());
                $propertyComplexType->setNillable($attribute->isNillable());
                $propertyComplexType->setIsAttribute($attribute->isAttribute());
                $propertyComplexType->setName($attribute->getName());
                $annotations['properties']->add($propertyComplexType);
            }
        }

        return $annotations;
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, ?string $type = null): bool
    {
        return \is_string($resource) && class_exists($resource) && 'annotation_complextype' === $type;
    }
}
