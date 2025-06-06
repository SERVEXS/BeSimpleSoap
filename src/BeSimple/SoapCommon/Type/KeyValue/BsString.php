<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class BsString extends AbstractKeyValue
{
    #[ComplexType(['name' => 'string'])]
    protected $value;
}
