<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class DateTime extends AbstractKeyValue
{
    #[ComplexType(['name' => 'dateTime'])]
    protected $value;
}
