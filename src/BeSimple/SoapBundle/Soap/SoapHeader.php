<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Soap;

class SoapHeader implements \Stringable
{
    public function __construct(private $namespace, private $name, private $data)
    {
    }

    public function __toString(): string
    {
        return (string) $this->data;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getData()
    {
        return $this->data;
    }

    public function toNativeSoapHeader()
    {
        return new \SoapHeader($this->namespace, $this->name, $this->data);
    }
}
