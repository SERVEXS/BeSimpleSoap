<?php

namespace BeSimple\SoapBundle\Tests\ServiceBinding\fixtures;

class Setters
{
    private $foo;

    private $bar;

    public function getFoo()
    {
        return $this->foo;
    }

    public function setFoo($foo): void
    {
        $this->foo = $foo;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function setBar($bar): void
    {
        $this->bar = $bar;
    }
}
