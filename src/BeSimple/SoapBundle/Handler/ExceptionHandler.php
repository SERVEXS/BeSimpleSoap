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

namespace BeSimple\SoapBundle\Handler;

use BeSimple\SoapServer\Exception\ReceiverSoapFault;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ExceptionHandler
{
    protected ?\SoapFault $soapFault = null;

    public function __construct(protected FlattenException $exception, protected $details = null)
    {
    }

    public function setSoapFault(\SoapFault $soapFault): void
    {
        $this->soapFault = $soapFault;
    }

    public function __call($method, $arguments): void
    {
        if (isset($this->soapFault)) {
            throw $this->soapFault;
        }

        $code = $this->exception->getStatusCode();

        throw new ReceiverSoapFault(
            Response::$statusTexts[$code] ?? '',
            null,
            $this->details
        );
    }
}
