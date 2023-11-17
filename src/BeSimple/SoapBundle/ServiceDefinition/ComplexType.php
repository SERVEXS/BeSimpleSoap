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

namespace BeSimple\SoapBundle\ServiceDefinition;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ComplexType
{
    private $name;
    private $value;
    private bool $isNillable = false;
    private bool $isAttribute = false;

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isNillable()
    {
        return $this->isNillable;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isAttribute()
    {
        return $this->isAttribute;
    }

    /**
     * @param bool $isAttribute
     *
     * @return $this
     */
    public function setIsAttribute($isAttribute)
    {
        $this->isAttribute = $isAttribute;

        return $this;
    }

    public function setNillable($isNillable): void
    {
        $this->isNillable = (bool) $isNillable;
    }
}
