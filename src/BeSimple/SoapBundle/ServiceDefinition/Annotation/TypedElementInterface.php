<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Annotation;

interface TypedElementInterface
{
    public function getPhpType();

    public function getXmlType();

    public function setPhpType($phpType);

    public function setXmlType($xmlType);
}
