<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceBinding;

use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapCommon\Definition\Type\ArrayOfType;
use BeSimple\SoapCommon\Definition\Type\ComplexType;
use BeSimple\SoapCommon\Definition\Type\TypeRepository;
use BeSimple\SoapCommon\Type\AbstractKeyValue;
use BeSimple\SoapCommon\Util\MessageBinder;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class RpcLiteralResponseMessageBinder implements MessageBinderInterface
{
    protected TypeRepository $typeRepository;

    private array $messageRefs = [];

    public function processMessage(Method $messageDefinition, $message, TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;

        $parts = $messageDefinition->getOutput()->all();
        $part = array_shift($parts);

        return $this->processType($part->getType(), $message);
    }

    private function processType($phpType, $message)
    {
        $isArray = false;

        $type = $this->typeRepository->getType($phpType);
        if ($type instanceof ArrayOfType) {
            $isArray = true;

            $type = $this->typeRepository->getType($type->get('item')->getType());
        }

        if ($type instanceof ComplexType) {
            $phpType = $type->getPhpType();

            if ($isArray) {
                $array = [];

                // See https://github.com/BeSimple/BeSimpleSoapBundle/issues/29
                if (\is_array($message) && \in_array(AbstractKeyValue::class, class_parents($phpType), true)) {
                    $keyValue = [];
                    foreach ($message as $key => $value) {
                        $keyValue[] = new $phpType($key, $value);
                    }

                    $message = $keyValue;
                }

                foreach ($message as $complexType) {
                    $array[] = $this->checkComplexType($phpType, $complexType);
                }

                $message = $array;
            } else {
                $message = $this->checkComplexType($phpType, $message);
            }
        }

        return $message;
    }

    private function checkComplexType($phpType, $message)
    {
        $hash = spl_object_hash($message);
        if (isset($this->messageRefs[$hash])) {
            return clone $this->messageRefs[$hash];
        }

        $this->messageRefs[$hash] = $message;

        if (!$message instanceof $phpType) {
            throw new \InvalidArgumentException(sprintf('The instance class must be "%s", "%s" given.', $phpType, $message::class));
        }

        $message = clone $message;
        $messageBinder = new MessageBinder($message);
        foreach ($this->typeRepository->getType($phpType)->all() as $type) {
            $property = $type->getName();
            $value = $messageBinder->readProperty($property);

            if (null !== $value) {
                $value = $this->processType($type->getType(), $value);

                $messageBinder->writeProperty($property, $value);
            }

            if (!$type->isNillable() && null === $value) {
                throw new \InvalidArgumentException(sprintf('"%s::%s" cannot be null.', $phpType, $type->getName()));
            }
        }

        return $message;
    }
}
