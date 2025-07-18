<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Soap;

use BeSimple\SoapBundle\Util\Collection;
use Laminas\Mime\Message;
use Symfony\Component\HttpFoundation\Request;

/**
 * SoapRequest.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapRequest extends Request
{
    protected ?string $soapMessage = null;

    protected string $soapAction;

    protected Collection $soapHeaders;

    protected Collection $soapAttachments;

    public static function createFromHttpRequest(Request $request): self
    {
        return new static(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->content
        );
    }

    public function initialize(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ): void {
        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);

        $this->soapMessage = null;
        $this->soapHeaders = new Collection('getName', SoapHeader::class);
        $this->soapAttachments = new Collection('getId', SoapAttachment::class);

        $this->setRequestFormat('soap');
    }

    /**
     * Gets the XML string of the SOAP message.
     *
     * @return string
     */
    public function getSoapMessage()
    {
        if (null === $this->soapMessage) {
            $this->soapMessage = $this->initializeSoapMessage();
        }

        return $this->soapMessage;
    }

    public function getSoapHeaders()
    {
        return $this->soapHeaders;
    }

    public function getSoapAttachments()
    {
        return $this->soapAttachments;
    }

    protected function initializeSoapMessage()
    {
        if ($this->server->has('CONTENT_TYPE')) {
            $type = $this->splitContentTypeHeader($this->server->get('CONTENT_TYPE'));

            switch ($type['_type']) {
                case 'multipart/related':
                    if ($type['type'] === 'application/xop+xml') {
                        return $this->initializeMtomSoapMessage($type, $this->getContent());
                    }
                    break;
                case 'application/soap+xml':
                    // goto fallback
                    break;
                default:
                    // log error
                    break;
            }
        }

        // fallback
        return $this->getContent();
    }

    protected function initializeMtomSoapMessage(array $contentTypeHeader, $content)
    {
        if (!isset($contentTypeHeader['start']) || !isset($contentTypeHeader['start-info']) || !isset($contentTypeHeader['boundary'])) {
            throw new \InvalidArgumentException();
        }

        $mimeMessage = Message::createFromMessage($content, $contentTypeHeader['boundary']);
        $mimeParts = $mimeMessage->getParts();

        $soapMimePartId = trim((string) $contentTypeHeader['start'], '<>');
        $soapMimePartType = $contentTypeHeader['start-info'];

        $rootPart = array_shift($mimeParts);
        $rootPartType = $this->splitContentTypeHeader($rootPart->type);

        // TODO: add more checks to achieve full compatibility to MTOM spec
        // http://www.w3.org/TR/soap12-mtom/
        if ($rootPart->id !== $soapMimePartId || $rootPartType['_type'] !== 'application/xop+xml' || $rootPartType['type'] !== $soapMimePartType) {
            throw new \InvalidArgumentException();
        }

        foreach ($mimeParts as $mimePart) {
            $this->soapAttachments->add(
                new SoapAttachment(
                    $mimePart->id,
                    $mimePart->type,
                    // handle content decoding / prevent encoding
                    $mimePart->getContent()
                )
            );
        }

        // handle content decoding / prevent encoding
        return $rootPart->getContent();
    }

    protected function splitContentTypeHeader($header)
    {
        $result = [];
        $parts = explode(';', strtolower((string) $header));

        $result['_type'] = array_shift($parts);

        foreach ($parts as $part) {
            [$key, $value] = explode('=', trim($part), 2);

            $result[$key] = trim($value, '"');
        }

        return $result;
    }
}
