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

use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapCommon\Definition\Type\TypeRepository;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class DocumentLiteralWrappedRequestHeaderMessageBinder extends DocumentLiteralWrappedRequestMessageBinder
{
    private $header;

    public function setHeader($header): void
    {
        $this->header = $header;
    }

    public function processMessage(Method $messageDefinition, $message, TypeRepository $typeRepository)
    {
        $headerDefinition = $messageDefinition->getHeaders()->get($this->header);
        return [];
    }
}
