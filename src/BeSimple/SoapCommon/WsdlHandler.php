<?php

/*
 * This file is part of BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon;

/**
 * This class loads the given WSDL file and allows to check MIME binding
 * information.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class WsdlHandler
{
    /**
     * Binding type 'input' .
     */
    final public const BINDING_OPERATION_INPUT = 'input';

    /**
     * Binding type 'output' .
     */
    final public const BINDING_OPERATION_OUTPUT = 'output';

    /**
     * DOMDocument WSDL file.
     *
     */
    private ?\DOMDocument $domDocument = null;

    /**
     * DOMXPath WSDL file.
     *
     * @var DOMXPath
     */
    private $domXpath;

    /**
     * Array of mime type information.
     *
     * @var array(string=>array(string=>array(string=>array(string))))
     */
    private array $mimeTypes = [];

    /**
     * WSDL namespace of current WSDL file.
     *
     */
    private ?string $wsdlSoapNamespace = null;

    /**
     * Constructor.
     *
     * @param string $wsdlFile    WSDL file name
     * @param string $soapVersion SOAP version constant
     */
    public function __construct(/**
     * WSDL file name.
     *
     */
    private $wsdlFile, $soapVersion)
    {
        if ($soapVersion == \SOAP_1_1) {
            $this->wsdlSoapNamespace = Helper::NS_WSDL_SOAP_1_1;
        } else {
            $this->wsdlSoapNamespace = Helper::NS_WSDL_SOAP_1_2;
        }
    }

    /**
     * Gets the mime type information from the WSDL file.
     *
     * @param string $soapAction Soap action to analyse
     *
     * @return array(string=>array(string=>array(string)))
     */
    private function getMimeTypesForSoapAction($soapAction)
    {
        $query = '/wsdl:definitions/wsdl:binding/wsdl:operation/soap:operation[@soapAction="' . $soapAction . '"]/..';
        $nodes = $this->domXpath->query($query);
        $mimeTypes = [];
        if (null !== $wsdlOperation = $nodes->item(0)) {
            // $wsdlOperationName = $wsdlOperation->getAttribute('name');
            foreach ($wsdlOperation->childNodes as $soapOperationChild) {
                // wsdl:input or wsdl:output
                if ($soapOperationChild->localName == 'input' || $soapOperationChild->localName == 'output') {
                    $operationType = $soapOperationChild->localName;
                    // mime:multipartRelated/mime:part
                    $mimeParts = $soapOperationChild->getElementsByTagNameNS(Helper::NS_WSDL_MIME, 'part');
                    if ($mimeParts->length > 0) {
                        foreach ($mimeParts as $mimePart) {
                            foreach ($mimePart->childNodes as $child) {
                                switch ($child->localName) {
                                    case 'body':
                                        $parts = $child->getAttribute('parts');
                                        $parts = ($parts == '') ? '[body]' : $parts;
                                        $mimeTypes[$operationType][$parts] = ['text/xml'];
                                        break;
                                    case 'content':
                                        $part = $child->getAttribute('part');
                                        $part = ($part == '') ? null : $part;
                                        $type = $child->getAttribute('type');
                                        $type = ($type == '') ? '*/*' : $type;
                                        if (!isset($mimeTypes[$operationType][$part])) {
                                            $mimeTypes[$operationType][$part] = [];
                                        }
                                        $mimeTypes[$operationType][$part][] = $type;
                                        break;
                                    case 'mimeXml':
                                        // this does not conform to the spec
                                        $part = $child->getAttribute('part');
                                        $part = ($part == '') ? null : $part;
                                        $mimeTypes[$operationType][$part] = ['text/xml'];
                                        break;
                                }
                            }
                        }
                    } else {
                        $child = $soapOperationChild->getElementsByTagNameNS($this->wsdlSoapNamespace, 'body')->item(0);
                        if (null !== $child) {
                            $parts = $child->getAttribute('parts');
                            $parts = ($parts == '') ? '[body]' : $parts;
                            $mimeTypes[$operationType][$parts] = ['text/xml'];
                        }
                    }
                }
            }
        }

        return $mimeTypes;
    }

    /**
     * Checks the mime type of the part for the given operation.
     *
     * @param string $soapAction      Soap action
     * @param string $operationType   Operation type
     * @param string $part            Part name
     * @param string $currentMimeType Current mime type
     *
     * @return bool
     */
    public function isValidMimeTypeType($soapAction, $operationType, $part, $currentMimeType)
    {
        // create DOMDocument from WSDL file
        $this->loadWsdlInDom();
        // load data from WSDL
        if (!isset($this->mimeTypes[$soapAction])) {
            $this->mimeTypes[$soapAction] = $this->getMimeTypesForSoapAction($soapAction);
        }
        // part is valid as we do not have an explicit entry for current part
        if (!isset($this->mimeTypes[$soapAction][$operationType][$part])) {
            return true;
        }
        $mimeTypes = $this->mimeTypes[$soapAction][$operationType][$part];
        // wildcard or exact match
        if (\in_array('*/*', $mimeTypes, true) || \in_array($currentMimeType, $mimeTypes, true)) {
            return true;
            // type/* match
        }

        [$ctype] = explode('/', $currentMimeType);
        foreach ($mimeTypes as $mimeType) {
            [$type, $subtype] = explode('/', (string) $mimeType);
            if ($subtype === '*' && $type === $ctype) {
                return true;
            }
        }

        return false;
    }

    /**
     * Loads the WSDL file into a DOM
     */
    private function loadWsdlInDom(): void
    {
        if (null === $this->domDocument) {
            $this->domDocument = new \DOMDocument('1.0', 'utf-8');
            $this->domDocument->load($this->wsdlFile);
            $this->domXpath = new \DOMXPath($this->domDocument);
            $this->domXpath->registerNamespace('wsdl', Helper::NS_WSDL);
            $this->domXpath->registerNamespace('mime', Helper::NS_WSDL_MIME);
            $this->domXpath->registerNamespace('soap', $this->wsdlSoapNamespace);
        }
    }
}
