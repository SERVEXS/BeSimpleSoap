<?php

namespace BeSimple\SoapCommon\Type;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType;

abstract class AbstractKeyValue
{
    public function __construct(
        #[ComplexType(['type' => 'string'])]
        protected $key,
        /**
         * The Soap type of this variable must be defined in child class
         */
        protected $value
    ) {
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }
}
