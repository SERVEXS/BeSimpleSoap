<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceBinding;

use BeSimple\SoapBundle\ServiceDefinition\Definition;
use BeSimple\SoapBundle\Soap\SoapHeader;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class ServiceBinder
{
    private Definition $definition;

    private MessageBinderInterface $requestHeaderMessageBinder;

    private MessageBinderInterface $requestMessageBinder;

    private MessageBinderInterface $responseMessageBinder;

    public function __construct(
        Definition $definition,
        MessageBinderInterface $requestHeaderMessageBinder,
        MessageBinderInterface $requestMessageBinder,
        MessageBinderInterface $responseMessageBinder
    ) {
        $this->definition = $definition;

        $this->requestHeaderMessageBinder = $requestHeaderMessageBinder;
        $this->requestMessageBinder = $requestMessageBinder;

        $this->responseMessageBinder = $responseMessageBinder;
    }

    /**
     * @param string $method
     * @param string $header
     *
     * @return bool
     */
    public function isServiceHeader($method, $header)
    {
        return $this->definition->getMethod($method)->getHeader($header);
    }

    /**
     * @return bool
     */
    public function isServiceMethod($method)
    {
        return null !== $this->definition->getMethod($method);
    }

    /**
     * @param string $method
     * @param string $header
     *
     * @return SoapHeader
     */
    public function processServiceHeader($method, $header, $data)
    {
        $methodDefinition = $this->definition->getMethod($method);
        $headerDefinition = $methodDefinition->getHeader($header);

        $this->requestHeaderMessageBinder->setHeader($header);
        $data = $this->requestHeaderMessageBinder->processMessage(
            $methodDefinition,
            $data,
            $this->definition->getTypeRepository()
        );

        return new SoapHeader($this->definition->getNamespace(), $headerDefinition->getName(), $data);
    }

    /**
     * @param string $method
     * @param array $arguments
     *
     * @return array
     */
    public function processServiceMethodArguments($method, $arguments)
    {
        $methodDefinition = $this->definition->getMethod($method);

        return array_merge(
            ['_controller' => $methodDefinition->getController()],
            $this->requestMessageBinder->processMessage($methodDefinition, $arguments, $this->definition->getTypeRepository())
        );
    }

    /**
     * @param string $name
     */
    public function processServiceMethodReturnValue($name, $return)
    {
        $methodDefinition = $this->definition->getMethod($name);

        return $this->responseMessageBinder->processMessage($methodDefinition, $return, $this->definition->getTypeRepository());
    }
}
