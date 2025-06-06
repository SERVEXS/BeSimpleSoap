<?php

/*
 * This file is part of the BeSimpleSoap.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Definition\Type;

use BeSimple\SoapCommon\Definition\Message;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ComplexType extends Message implements TypeInterface
{
    /**
     * @var string
     */
    private $phpType;

    /**
     * @var array|string|string[]
     */
    private $xmlType;

    /**
     * @param string $phpType
     * @param string|null $xmlType
     */
    public function __construct($phpType, $xmlType)
    {
        $this->phpType = $phpType;
        $this->xmlType = str_replace('\\', '.', (string) $xmlType);

        parent::__construct($xmlType);
    }

    public function getPhpType()
    {
        return $this->phpType;
    }

    public function getXmlType()
    {
        return $this->xmlType;
    }
}
