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

namespace BeSimple\SoapCommon;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Cache
{
    final public const DISABLED = 0;
    final public const ENABLED = 1;

    final public const TYPE_NONE = \WSDL_CACHE_NONE;
    final public const TYPE_DISK = \WSDL_CACHE_DISK;
    final public const TYPE_MEMORY = \WSDL_CACHE_MEMORY;
    final public const TYPE_DISK_MEMORY = \WSDL_CACHE_BOTH;

    protected static $types = [
        self::TYPE_NONE,
        self::TYPE_DISK,
        self::TYPE_MEMORY,
        self::TYPE_DISK_MEMORY,
    ];

    public static function getTypes()
    {
        return self::$types;
    }

    public static function isEnabled()
    {
        return self::iniGet('soap.wsdl_cache_enabled');
    }

    public static function setEnabled($enabled): void
    {
        if (!\in_array($enabled, [self::ENABLED, self::DISABLED], true)) {
            throw new \InvalidArgumentException();
        }

        self::iniSet('soap.wsdl_cache_enabled', $enabled);
    }

    public static function getType()
    {
        return self::iniGet('soap.wsdl_cache');
    }

    public static function setType($type): void
    {
        if (!\in_array($type, self::getTypes(), true)) {
            throw new \InvalidArgumentException('The cache type has to be either Cache::TYPE_NONE, Cache::TYPE_DISK, Cache::TYPE_MEMORY or Cache::TYPE_DISK_MEMORY');
        }

        self::iniSet('soap.wsdl_cache', $type);
    }

    public static function getDirectory()
    {
        return self::iniGet('soap.wsdl_cache_dir');
    }

    public static function setDirectory($directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        self::iniSet('soap.wsdl_cache_dir', $directory);
    }

    public static function getLifetime()
    {
        return self::iniGet('soap.wsdl_cache_ttl');
    }

    public static function setLifetime($lifetime): void
    {
        self::iniSet('soap.wsdl_cache_ttl', $lifetime);
    }

    public static function getLimit()
    {
        return self::iniGet('soap.wsdl_cache_limit');
    }

    public static function setLimit($limit): void
    {
        self::iniSet('soap.wsdl_cache_limit', $limit);
    }

    protected static function iniGet($key)
    {
        return \ini_get($key);
    }

    protected static function iniSet($key, $value): void
    {
        ini_set($key, $value);
    }
}
