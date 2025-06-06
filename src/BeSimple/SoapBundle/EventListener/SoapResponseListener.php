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

namespace BeSimple\SoapBundle\EventListener;

use BeSimple\SoapBundle\Soap\SoapRequest;
use BeSimple\SoapBundle\Soap\SoapResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;

/**
 * SoapResponseListener.
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class SoapResponseListener
{
    public function __construct(protected SoapResponse $response)
    {
    }

    /**
     * Set the controller result in SoapResponse.
     */
    public function onKernelView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request instanceof SoapRequest) {
            return;
        }

        $this->response->setReturnValue($event->getControllerResult());
        $event->setResponse($this->response);
    }
}
