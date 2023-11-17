<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class Date extends AbstractKeyValue
{
    #[ComplexType(['type' => 'date'])]
    protected $value;
}
