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
 * @author Christian Kerl <christian-kerl@web.de>
 */
class QName implements \Stringable
{
    public static function isPrefixedQName($qname)
    {
        return str_contains((string) $qname, ':') ? true : false;
    }

    public static function fromPrefixedQName($qname, $resolveNamespacePrefixCallable)
    {
        Assert::thatArgument('qname', self::isPrefixedQName($qname));

        [$prefix, $name] = explode(':', (string) $qname);

        return new self(\call_user_func($resolveNamespacePrefixCallable, $prefix), $name);
    }

    public static function fromPackedQName($qname)
    {
        Assert::thatArgument('qname', preg_match('/^\{(.+)\}(.+)$/', (string) $qname, $matches));

        return new self($matches[1], $matches[2]);
    }

    public function __construct(private $namespace, private $name)
    {
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return sprintf('{%s}%s', $this->getNamespace(), $this->getName());
    }
}
