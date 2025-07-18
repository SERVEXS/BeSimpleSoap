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
 * String provides utility methods for strings.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class BsString
{
    /**
     * Checks if a string starts with a given string.
     *
     * @param  string $str    A string
     * @param  string $substr A string to check against
     */
    public static function startsWith($str, $substr): bool
    {
        if (\is_string($str) && \is_string($substr) && \strlen($str) >= \strlen($substr)) {
            return str_starts_with($str, $substr);
        }

        return false;
    }

    /**
     * Checks if a string ends with a given string.
     *
     * @param  string $str    A string
     * @param  string $substr A string to check against
     */
    public static function endsWith($str, $substr): bool
    {
        if (\is_string($str) && \is_string($substr) && \strlen($str) >= \strlen($substr)) {
            return $substr === substr($str, \strlen($str) - \strlen($substr));
        }

        return false;
    }
}
