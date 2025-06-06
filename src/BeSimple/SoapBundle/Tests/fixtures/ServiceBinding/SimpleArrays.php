<?php

namespace BeSimple\SoapBundle\Tests\fixtures\ServiceBinding;

class SimpleArrays
{
    public function __construct(public $array1, private $array2, private $array3)
    {
    }

    public function getArray2()
    {
        return $this->array2;
    }

    public function getArray3()
    {
        return $this->array3;
    }
}
