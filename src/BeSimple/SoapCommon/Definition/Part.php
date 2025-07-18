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

namespace BeSimple\SoapCommon\Definition;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Part
{
    protected $nillable;
    protected $attribute;

    public function __construct(protected $name, protected $type, $nillable = false, $attribute = false)
    {
        $this->setNillable($nillable);
        $this->setAttribute($attribute);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param bool $attribute
     *
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function isNillable()
    {
        return $this->nillable;
    }

    public function setNillable($nillable): void
    {
        $this->nillable = (bool) $nillable;
    }
}
