<?php

/*
 * This file is part of the BeSimpleSoap.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Definition;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Method
{
    private readonly \BeSimple\SoapCommon\Definition\Message $headers;
    private readonly \BeSimple\SoapCommon\Definition\Message $input;
    private readonly \BeSimple\SoapCommon\Definition\Message $output;
    private readonly \BeSimple\SoapCommon\Definition\Message $fault;

    public function __construct(private $name)
    {
        $this->headers = new Message($name . 'Header');
        $this->input = new Message($name . 'Request');
        $this->output = new Message($name . 'Response');
        $this->fault = new Message($name . 'Fault');
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function getVersions()
    {
        return [\SOAP_1_1, \SOAP_1_2];
    }

    public function getUse()
    {
        return \SOAP_LITERAL;
    }

    public function addHeader($name, $type): void
    {
        $this->headers->add($name, $type);
    }

    public function addInput($name, $type): void
    {
        $this->input->add($name, $type);
    }

    public function setOutput($type, $name = 'return'): void
    {
        $this->output->add($name, $type);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name, $default = null)
    {
        return $this->headers->get($name, $default);
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getFault()
    {
        return $this->fault;
    }
}
