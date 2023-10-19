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
use Symfony\Component\HttpFoundation\Response;

/**
 * SoapResponse.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapResponse extends Response
{
    protected Collection $soapHeaders;

    /**
     * @var mixed|null
     */
    protected $soapReturnValue;

    public function __construct($returnValue = null)
    {
        parent::__construct();

        $this->soapHeaders = new Collection('getName', SoapHeader::class);
        $this->setReturnValue($returnValue);
    }

    public function addSoapHeader(SoapHeader $soapHeader): void
    {
        $this->soapHeaders->add($soapHeader);
    }

    public function getSoapHeaders(): Collection
    {
        return $this->soapHeaders;
    }

    /**
     * @param mixed|null $value
     */
    public function setReturnValue($value): SoapResponse
    {
        $this->soapReturnValue = $value;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getReturnValue()
    {
        return $this->soapReturnValue;
    }
}
