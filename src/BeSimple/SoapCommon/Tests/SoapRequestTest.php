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

namespace BeSimple\SoapCommon\Tests;

use BeSimple\SoapCommon\Cache;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

class SoapRequestTest extends TestCase
{
    public function testSetEnabled(): void
    {
        Cache::setEnabled(Cache::ENABLED);
        $this->assertEquals(Cache::ENABLED, Cache::isEnabled());

        Cache::setEnabled(Cache::DISABLED);
        $this->assertEquals(Cache::DISABLED, Cache::isEnabled());
    }

    public function testSetEnabledBadValue(): void
    {
        $this->expectException('InvalidArgumentException');
        Cache::setEnabled('foo');
    }

    public function testSetType(): void
    {
        Cache::setType(Cache::TYPE_DISK);
        $this->assertEquals(Cache::TYPE_DISK, Cache::getType());

        Cache::setType(Cache::TYPE_NONE);
        $this->assertEquals(Cache::TYPE_NONE, Cache::getType());
    }

    public function testSetTypeBadValue(): void
    {
        $this->expectException('InvalidArgumentException');
        Cache::setType('foo');
    }

    public function testSetDirectory(): void
    {
        vfsStream::setup('Fixtures');

        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('foo'));
        $dir = vfsStream::url('Fixtures/foo');
        Cache::setDirectory($dir);
        $this->assertEquals($dir, Cache::getDirectory());
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('foo'));

        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('bar'));
        $dir = vfsStream::url('Fixtures/bar');
        Cache::setDirectory($dir);
        $this->assertEquals($dir, Cache::getDirectory());
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('bar'));
    }

    public function testSetLifetime(): void
    {
        Cache::setLifetime(1234);
        $this->assertEquals(1234, Cache::getLifetime());

        Cache::setLifetime(4321);
        $this->assertEquals(4321, Cache::getLifetime());
    }

    public function testSetLimit(): void
    {
        Cache::setLimit(10);
        $this->assertEquals(10, Cache::getLimit());

        Cache::setLimit(1);
        $this->assertEquals(1, Cache::getLimit());
    }

    public function setUp(): void
    {
        ini_restore('soap.wsdl_cache_enabled');
        ini_restore('soap.wsdl_cache');
        ini_restore('soap.wsdl_cache_dir');
        ini_restore('soap.wsdl_cache_ttl');
        ini_restore('soap.wsdl_cache_limit');
    }
}
