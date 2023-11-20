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

use BeSimple\SoapBundle\ServiceDefinition\Annotation;
use BeSimple\SoapBundle\ServiceDefinition\Annotation\Method;
use BeSimple\SoapBundle\ServiceDefinition as Definition;
use BeSimple\SoapCommon\Definition\Type\ComplexType;
use BeSimple\SoapCommon\Definition\Type\TypeRepository;
use Doctrine\Common\Annotations\Reader;
use Exception;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderResolverInterface;

use function is_string;
use function sprintf;

/**
 * AnnotationClassLoader loads ServiceDefinition from a PHP class and its methods.
 *
 * Based on \Symfony\Component\Routing\Loader\AnnotationClassLoader
 *
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class AnnotationClassLoader extends Loader
{
    public function __construct(protected Reader $reader, protected TypeRepository $typeRepository)
    {
    }

    /**
     * Loads a ServiceDefinition from annotations from a class.
     *
     * @param string $class A class name
     * @param string $type The resource type
     *
     * @return Definition\Definition A ServiceDefinition instance
     *
     * @throws InvalidArgumentException When route can't be parsed
     * @throws Exception
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new ReflectionClass($class);
        $definition = new Definition\Definition($this->typeRepository);

        $sharedHeaders = [];
        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Annotation\Header) {
                $sharedHeaders[$annotation->getValue()] = $this->loadType($annotation->getPhpType());
            }
        }

        foreach ($class->getAttributes() as $attribute) {
            if ($attribute instanceof Annotation\Header) {
                $sharedHeaders[$attribute->getValue()] = $this->loadType($attribute->getPhpType());
            }
        }

        foreach ($class->getMethods() as $method) {
            $serviceHeaders = $sharedHeaders;
            $serviceArguments = [];
            $serviceMethod = null;
            $serviceReturn = null;

            // Legacy method annotation processing...
            foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                if ($annotation instanceof Annotation\Header) {
                    $serviceHeaders[$annotation->getValue()] = $this->loadType($annotation->getPhpType());
                } elseif ($annotation instanceof Annotation\Param) {
                    $serviceArguments[$annotation->getValue()] = $this->loadType($annotation->getPhpType());
                } elseif ($annotation instanceof Method) {
                    if ($serviceMethod) {
                        throw new LogicException(sprintf('Soap\Method defined twice for "%s".', $method->getName()));
                    }

                    $serviceMethod = new Definition\Method(
                        $annotation->getValue(),
                        $this->getController($class, $method, $annotation)
                    );
                } elseif ($annotation instanceof Annotation\Result) {
                    if ($serviceReturn) {
                        throw new LogicException(sprintf('Soap\Result defined twice for "%s".', $method->getName()));
                    }

                    $serviceReturn = $annotation->getPhpType();
                    $serviceXmlReturn = $annotation->getXmlType();
                }
            }

            // method attribute processing...
            foreach ($method->getAttributes() as $attribute){
                if ($attribute instanceof Annotation\Header) {
                    $serviceHeaders[$attribute->getValue()] = $this->loadType($attribute->getPhpType());
                } else if ($attribute instanceof Annotation\Param) {
                    $serviceArguments[$attribute->getValue()] = $this->loadType($attribute->getPhpType());
                } else if ($attribute instanceof Method) {
                    if ($serviceMethod) {
                        throw new LogicException(sprintf('Soap\Method defined twice for "%s".', $method->getName()));
                    }

                    $serviceMethod = new Definition\Method(
                        $attribute->getValue(),
                        $this->getController($class, $method, $attribute)
                    );
                } else if ($attribute instanceof Annotation\Result) {
                    if ($serviceReturn) {
                        throw new LogicException(sprintf('Soap\Result defined twice for "%s".', $method->getName()));
                    }

                    $serviceReturn = $attribute->getPhpType();
                    $serviceXmlReturn = $attribute->getXmlType();
                }
            }

            if (!$serviceMethod && (!empty($serviceArguments) || $serviceReturn)) {
                throw new LogicException(sprintf('@Soap\Method non-existent for "%s".', $method->getName()));
            }

            if ($serviceMethod) {
                foreach ($serviceHeaders as $name => $headerType) {
                    $serviceMethod->addHeader($name, $headerType);
                }

                foreach ($serviceArguments as $name => $serviceType) {
                    $serviceMethod->addInput($name, $serviceType);
                }

                if (!$serviceReturn) {
                    throw new LogicException(sprintf('Soap\Result non-existent for "%s".', $method->getName()));
                }

                if (!isset($serviceXmlReturn) || !$serviceXmlReturn) {
                    $serviceXmlReturn = 'return';
                }

                $serviceMethod->setOutput($this->loadType($serviceReturn), $serviceXmlReturn);

                $definition->addMethod($serviceMethod);
            }
        }

        return $definition;
    }

    private function getController(ReflectionClass $class, ReflectionMethod $reflectionMethod, Method $method): string
    {
        if (null !== $method->getService()) {
            return $method->getService() . ':' . $reflectionMethod->name;
        }

        return $class->name . '::' . $reflectionMethod->name;
    }

    private function loadType($phpType)
    {
        if (false !== $arrayOf = $this->typeRepository->getArrayOf($phpType)) {
            $this->loadType($arrayOf);
        }

        if (!$this->typeRepository->hasType($phpType)) {
            $complexTypeResolver = $this->resolve($phpType, 'annotation_complextype');
            if (!$complexTypeResolver) {
                throw new Exception();
            }

            $loaded = $complexTypeResolver->load($phpType);
            $complexType = new ComplexType($phpType, $loaded['alias'] ?? $phpType);
            foreach ($loaded['properties'] as $name => $property) {
                $complexType->add($name, $this->loadType($property->getValue()), $property->isNillable(), $property->isAttribute());
            }

            $this->typeRepository->addComplexType($complexType);
        }

        return $phpType;
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, ?string $type = null): bool
    {
        return is_string($resource) && preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource) && (!$type || 'annotation' === $type);
    }

    public function getResolver(): LoaderResolverInterface
    {
        return $this->resolver;
    }
}
