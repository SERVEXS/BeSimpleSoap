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

    public function __construct($message)
    {
        if (!is_object($message)) {
            throw new \InvalidArgumentException(sprintf('The message must be an object, %s given', gettype($message)));
        }

        $this->message = $message;
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
        $this->propertyAccessor->setValue($this->message, $property, $value);
    }
}
