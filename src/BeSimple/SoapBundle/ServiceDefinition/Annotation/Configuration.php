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

/**
 * Based on \Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation
 *
 * @author Francis Besset <francis.besset@gmail.com>
 *
 * @template TConfiguration of array
 */
abstract class Configuration implements ConfigurationInterface
{
    /**
     * @param TConfiguration $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $k => $v) {
            if (!method_exists($this, $name = 'set' . $k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, self::class));
            }

            $this->$name($v);
        }
    }
}
