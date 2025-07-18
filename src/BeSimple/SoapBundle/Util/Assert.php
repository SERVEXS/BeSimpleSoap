<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Util;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class Assert
{
    final public const ARGUMENT_INVALID = 'Argument "%s" is invalid.';
    final public const ARGUMENT_NULL = 'Argument "%s" can not be null.';

    public static function thatArgument($name, $condition, $message = self::ARGUMENT_INVALID): void
    {
        if (!$condition) {
            throw new \InvalidArgumentException(sprintf($message, $name));
        }
    }

    public static function thatArgumentNotNull($name, $value): void
    {
        self::thatArgument($name, null !== $value, self::ARGUMENT_NULL);
    }
}
