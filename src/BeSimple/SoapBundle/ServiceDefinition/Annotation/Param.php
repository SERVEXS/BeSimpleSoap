<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Annotation;

/**
 * @Annotation
 *
 * @phpstan-type TParam array{value: string, phpType: string, xmlType?: string}
 *
 * @extends Configuration<TParam>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Param extends Configuration implements TypedElementInterface
{
    private $value;

    private $phpType;

    private $xmlType;

    public function getValue()
    {
        return $this->value;
    }

    public function getPhpType()
    {
        return $this->phpType;
    }

    public function getXmlType()
    {
        return $this->xmlType;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function setPhpType($phpType): void
    {
        $this->phpType = $phpType;
    }

    public function setXmlType($xmlType): void
    {
        $this->xmlType = $xmlType;
    }

    public function getAliasName()
    {
        return 'param';
    }
}
