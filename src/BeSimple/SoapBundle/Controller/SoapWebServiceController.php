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

namespace BeSimple\SoapBundle\Controller;

use BeSimple\SoapBundle\Handler\ExceptionHandler;
use BeSimple\SoapBundle\ServiceBinding\ServiceBinder;
use BeSimple\SoapBundle\Soap\SoapRequest;
use BeSimple\SoapBundle\Soap\SoapResponse;
use BeSimple\SoapBundle\WebServiceContext;
use BeSimple\SoapServer\SoapServerBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class SoapWebServiceController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected \SoapServer $soapServer;

    protected SoapRequest $soapRequest;

    protected ?SoapResponse $soapResponse = null;

    protected ServiceBinder $serviceBinder;

    private array $headers = [];

    public function callAction($webservice): SoapResponse
    {
        /** @var WebServiceContext $webServiceContext */
        $webServiceContext = $this->getWebServiceContext($webservice);

        $this->serviceBinder = $webServiceContext->getServiceBinder();

        $this->soapRequest = SoapRequest::createFromHttpRequest($this->container->get('request_stack')->getCurrentRequest());
        $this->soapServer = $webServiceContext
            ->getServerBuilder()
            ->withSoapVersion11()
            ->withHandler($this)
            ->build();

        ob_start();
        $this->soapServer->handle($this->soapRequest->getSoapMessage());

        $response = $this->getResponse();
        $response->setContent(ob_get_clean());

        // The Symfony 2.0 Response::setContent() does not return the Response instance
        return $response;
    }

    public function definitionAction($webservice): Response
    {
        $routeName = $webservice . '_webservice_call';
        $result = $this->container->get('router')->getRouteCollection()->get($routeName);
        if ($result === null) {
            $routeName = '_webservice_call';
        }

        $response = new Response(
            $this->getWebServiceContext($webservice)->getWsdlFileContent(
                $this->container->get('router')->generate(
                    $routeName,
                    ['webservice' => $webservice],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            )
        );

        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $query = $request->query;
        if ($query->has('wsdl') || $query->has('WSDL')) {
            $request->setRequestFormat('wsdl');
        }

        return $response;
    }

    /**
     * Converts an Exception to a SoapFault Response.
     *
     * @throws \LogicException When the request query parameter "_besimple_soap_webservice" does not exist
     */
    public function exceptionAction(Request $request, FlattenException $exception, ?DebugLoggerInterface $logger = null): Response
    {
        if (!$webservice = $request->query->get('_besimple_soap_webservice')) {
            throw new \LogicException(
                \sprintf(
                    'The parameter "%s" is required in Request::$query parameter bag to generate the SoapFault.',
                    '_besimple_soap_webservice'
                ),
                null,
                $exception
            );
        }

        $view = '@Twig/Exception/' . ($this->container->get('kernel')->isDebug() ? 'exception' : 'error') . '.txt.twig';
        $code = $exception->getStatusCode();
        $details = $this->container->get('twig')->render($view, [
            'status_code' => $code,
            'status_text' => Response::$statusTexts[$code] ?? '',
            'exception' => $exception,
            'logger' => $logger,
        ]);

        $handler = new ExceptionHandler($exception, $details);
        if ($soapFault = $request->query->get('_besimple_soap_fault')) {
            $handler->setSoapFault($soapFault);

            // Remove parameter from query because cannot be Serialized in Logger
            $request->query->remove('_besimple_soap_fault');
        }

        $server = SoapServerBuilder::createWithDefaults()
            ->withWsdl(__DIR__ . '/../Handler/wsdl/exception.wsdl')
            ->withWsdlCacheNone()
            ->withHandler($handler)
            ->build();

        ob_start();
        $server->handle(
            '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns="http://besim.pl/soap/exception/1.0/">' .
            '<soapenv:Header/>' .
            '<soapenv:Body>' .
            '<ns:exception />' .
            '</soapenv:Body>' .
            '</soapenv:Envelope>'
        );

        return new Response(ob_get_clean());
    }

    /**
     * This method gets called once for every SOAP header the \SoapServer received
     * and afterwards once for the called SOAP operation.
     *
     * @param string $method The SOAP header or SOAP operation name
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        if ($this->serviceBinder->isServiceMethod($method)) {
            // @TODO Add all SoapHeaders in SoapRequest
            foreach ($this->headers as $name => $value) {
                if ($this->serviceBinder->isServiceHeader($method, $name)) {
                    $this->soapRequest->getSoapHeaders()->add($this->serviceBinder->processServiceHeader($method, $name, $value));
                }
            }
            $this->headers = [];

            $this->soapRequest->attributes->add(
                $this->serviceBinder->processServiceMethodArguments($method, $arguments)
            );

            // forward to controller
            $response = $this->container->get('http_kernel')->handle($this->soapRequest, HttpKernelInterface::SUB_REQUEST, false);

            $this->setResponse($response);

            // add response soap headers to soap server
            foreach ($response->getSoapHeaders() as $header) {
                $this->soapServer->addSoapHeader($header->toNativeSoapHeader());
            }

            // return operation return value to soap server
            return $this->serviceBinder->processServiceMethodReturnValue(
                $method,
                $response->getReturnValue()
            );
        }

        // collect request soap headers
        $this->headers[$method] = $arguments[0];
    }

    protected function getRequest(): SoapRequest
    {
        return $this->soapRequest;
    }

    protected function getResponse(): SoapResponse
    {
        if (!$this->soapResponse) {
            $this->soapResponse = $this->container->get('besimple.soap.response');
        }

        return $this->soapResponse;
    }

    /**
     * Set the SoapResponse
     *
     * @throws \InvalidArgumentException If the given Response is not an instance of SoapResponse
     */
    protected function setResponse(Response $response): SoapResponse
    {
        if (!$response instanceof SoapResponse) {
            throw new \InvalidArgumentException('You must return an instance of BeSimple\SoapBundle\Soap\SoapResponse');
        }

        return $this->soapResponse = $response;
    }

    protected function getWebServiceContext($webservice): ?object
    {
        $context = \sprintf('besimple.soap.context.%s', $webservice);

        if ($this->container->has($context)) {
            return $this->container->get($context);
        }

        $context = \sprintf('besimple.soap.context.%s', \ucfirst($webservice));
        if (!$this->container->has($context)) {
            throw new NotFoundHttpException(
                \sprintf('No WebService with name "%s" found. Possible cause: case sensitivity.', $webservice)
            );
        }

        return $this->container->get($context);
    }
}
