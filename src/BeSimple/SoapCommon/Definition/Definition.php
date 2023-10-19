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

use BeSimple\SoapCommon\Definition\Type\TypeRepository;
use Exception;
use InvalidArgumentException;

use const SOAP_1_1;
use const SOAP_LITERAL;
use const SOAP_RPC;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Definition
{
    protected $name;

    protected $namespace;

    protected TypeRepository $typeRepository;

    protected array $options;

    protected array $methods;

    protected array $types;

    public function __construct($name, $namespace, TypeRepository $typeRepository, array $options = [])
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->methods = [];

        $this->typeRepository = $typeRepository;

        $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        $this->options = [
            'version' => SOAP_1_1,
            'style' => SOAP_RPC,
            'use' => SOAP_LITERAL,
            'location' => null,
        ];

        $invalid = [];
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new InvalidArgumentException(
                sprintf('The Definition does not support the following options: "%s"', implode('", "', $invalid))
            );
        }

        return $this;
    }

    public function setOption($key, $value)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new InvalidArgumentException(sprintf('The Definition does not support the "%s" option.', $key));
        }

        $this->options[$key] = $value;

        return $this;
    }

    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new InvalidArgumentException(sprintf('The Definition does not support the "%s" option.', $key));
        }

        return $this->options[$key];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getType($phpType)
    {
        return $this->types[$phpType];
    }

    public function addType($phpType, $xmlType)
    {
        if (isset($this->types[$phpType])) {
            throw new Exception();
        }

        $this->types[$phpType] = $xmlType;
    }

    public function getMessages()
    {
        $messages = array();
        foreach ($this->methods as $method) {
            $messages[] = $method->getHeaders();
            $messages[] = $method->getInput();
            $messages[] = $method->getOutput();
        }

        return $messages;
    }

    public function getMethod($name, $default = null)
    {
        return $this->methods[$name] ?? $default;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function addMethod(Method $method)
    {
        $name = $method->getName();
        if (isset($this->methods[$name])) {
            throw new Exception(sprintf('The method "%s" already exists', $name));
        }

        $this->methods[$name] = $method;

        return $method;
    }

    public function getTypeRepository(): TypeRepository
    {
        return $this->typeRepository;
    }
}
