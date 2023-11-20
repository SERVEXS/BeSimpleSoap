<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class BsInt extends AbstractKeyValue
{
    #[ComplexType(['name' => 'int'])]
    protected $value;
}
