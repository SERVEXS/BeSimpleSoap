<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Util;

use BeSimple\SoapCommon\PropertyAccess\ReflectionPropertyAccessor;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class MessageBinder
{
    /**
     * @var Object
     */
    protected $message;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var ReflectionClass
     */
    protected $reflectionClass;

    public function __construct($message)
    {
        if (!is_object($message)) {
            throw new InvalidArgumentException(sprintf('The message must be an object, %s given', gettype($message)));
        }

        $this->message = $message;
        $this->reflectionClass = new ReflectionClass($this->message);
        $this->propertyAccessor = new ReflectionPropertyAccessor(
            PropertyAccess::createPropertyAccessor()
        );
    }

    public function readProperty($property)
    {
        return $this->propertyAccessor->getValue($this->message, $property);
    }

    public function writeProperty($property, $value)
    {
        if ($this->reflectionClass->hasMethod($setter = 'set'.$property)) {
            if (!$this->reflectionClass->getMethod($setter)->isPublic()) {
                throw new RuntimeException(sprintf('Method "%s()" is not public in class "%s"', $setter, $this->reflectionClass->name));
            }

            $this->message->{$setter}($value);
        } elseif ($this->reflectionClass->hasMethod('__set')) {
            // needed to support magic method __set
            $this->message->{$property} = $value;
        } elseif ($this->reflectionClass->hasProperty($property)) {
            $p = $this->reflectionClass->getProperty($property);
            if (!$p->isPublic()) {
                $p->setAccessible(true);
            }

            $p->setValue($this->message, $value);
        } elseif (property_exists($this->message, $property)) {
            // needed to support \stdClass instances
            $this->message->{$property} = $value;
        }
    }
}
