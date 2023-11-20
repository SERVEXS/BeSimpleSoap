<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class BsBoolean extends AbstractKeyValue
{
    #[ComplexType(['name' => 'boolean'])]
    protected $value;
}
