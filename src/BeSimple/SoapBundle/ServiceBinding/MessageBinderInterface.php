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
 * @author Christian Kerl <christian-kerl@web.de>
 */
interface MessageBinderInterface
{
    public function processMessage(Method $messageDefinition, $message, TypeRepository $typeRepository);
}
