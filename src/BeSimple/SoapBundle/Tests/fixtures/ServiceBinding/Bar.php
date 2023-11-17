<?php

namespace BeSimple\SoapBundle\Tests\fixtures\ServiceBinding;

class Bar
{
    public function __construct(private $foo, private $bar)
    {
    }
}
