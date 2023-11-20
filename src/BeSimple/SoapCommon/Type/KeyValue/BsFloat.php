<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class BsFloat extends AbstractKeyValue
{
    #[ComplexType(['name' => 'float'])]
    protected $value;
}
