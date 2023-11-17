<?php

namespace BeSimple\SoapBundle\Tests\fixtures\ServiceBinding;

class BarRecursive
{
    public function __construct(private readonly FooRecursive $foo)
    {
    }
}
