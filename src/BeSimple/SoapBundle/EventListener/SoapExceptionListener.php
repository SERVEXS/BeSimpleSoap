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

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class SoapExceptionListener extends ErrorListener
{
    /**
     * To avoid conflict between , the logger param is not typed:
     *  The parent class needs and instance of `Psr\Log\LoggerInterface` from Symfony 2.2,
     *  before logger is an instance of `Symfony\Component\HttpKernel\Log\LoggerInterface`.
     *
     * @param ContainerInterface $container  A ContainerInterface instance
     * @param string             $controller The controller name to call
     * @param LoggerInterface    $logger     A logger instance
     */
    public function __construct(protected ContainerInterface $container, $controller, $logger)
    {
        parent::__construct($controller, $logger);
    }

    public function onKernelException(ExceptionEvent $event, ?string $eventName = null, ?EventDispatcherInterface $eventDispatcher = null): void
    {

        if (!$this->getIsMainRequest($event)) {
            return;
        }

        $request = $event->getRequest();
        if (!\in_array($request->getRequestFormat(), ['soap', 'xml'])) {
            return;
        }

        if ('xml' === $request->getRequestFormat() && '_webservice_call' !== $request->attributes->get('_route')) {
            return;
        }

        $attributes = $request->attributes;
        if (!$webservice = $attributes->get('webservice')) {
            return;
        }

        if (!$this->container->has(sprintf('besimple.soap.context.%s', $webservice))) {
            return;
        }

        // hack to retrieve the current WebService name in the controller
        $request->query->set('_besimple_soap_webservice', $webservice);

        $throwable = $event->getThrowable();
        if ($throwable instanceof \SoapFault) {
            $request->query->set('_besimple_soap_fault', $throwable);
        }

        parent::onKernelException($event, $eventName, $eventDispatcher);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Must be called before ExceptionListener of HttpKernel component
            KernelEvents::EXCEPTION => ['onKernelException', -64],
        ];
    }

    /**
     *
     * @return bool
     */
    public function getIsMainRequest(ExceptionEvent $event): bool
    {
        return $event->isMainRequest();
    }
}
