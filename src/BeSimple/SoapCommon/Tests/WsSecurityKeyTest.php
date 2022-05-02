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

use BeSimple\SoapCommon\WsSecurityKey;
use ass\XmlSecurity\Key as XmlSecurityKey;
use PHPUnit\Framework\TestCase;

class WsSecurityKeyTest extends TestCase
{
    public function testHasKeys(): void
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(XmlSecurityKey::RSA_SHA1, $filename);
        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientcert.pem';
        $wsk->addPublicKey(XmlSecurityKey::RSA_SHA1, $filename);

        $this->assertTrue($wsk->hasKeys());
        $this->assertTrue($wsk->hasPrivateKey());
        $this->assertTrue($wsk->hasPublicKey());
    }

    public function testHasKeysNone(): void
    {
        $wsk = new WsSecurityKey();

        $this->assertFalse($wsk->hasKeys());
        $this->assertFalse($wsk->hasPrivateKey());
        $this->assertFalse($wsk->hasPublicKey());
    }

    public function testHasPrivateKey(): void
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(XmlSecurityKey::RSA_SHA1, $filename);

        $this->assertFalse($wsk->hasKeys());
        $this->assertTrue($wsk->hasPrivateKey());
    }

    public function testHasPublicKey(): void
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientcert.pem';
        $wsk->addPublicKey(XmlSecurityKey::RSA_SHA1, $filename);

        $this->assertFalse($wsk->hasKeys());
        $this->assertTrue($wsk->hasPublicKey());
    }

    public function testAddPrivateKey(): void
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(XmlSecurityKey::RSA_SHA1, $filename);

        $this->assertTrue($wsk->hasPrivateKey());
        $this->assertInstanceOf(XmlSecurityKey::class, $wsk->getPrivateKey());
    }

    public function testAddPrivateKeySessionKey(): void
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(XmlSecurityKey::TRIPLEDES_CBC);

        $this->assertTrue($wsk->hasPrivateKey());
        $this->assertInstanceOf(XmlSecurityKey::class, $wsk->getPrivateKey());
    }

    public function testAddPrivateKeyNoFile(): void
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(XmlSecurityKey::RSA_SHA1, file_get_contents($filename), false);

        $this->assertTrue($wsk->hasPrivateKey());
        $this->assertInstanceOf(XmlSecurityKey::class, $wsk->getPrivateKey());
    }

    public function testAddPublicKey(): void
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientcert.pem';
        $wsk->addPublicKey(XmlSecurityKey::RSA_SHA1, $filename);

        $this->assertTrue($wsk->hasPublicKey());
        $this->assertInstanceOf(XmlSecurityKey::class, $wsk->getPublicKey());
    }

    public function testAddPublicKeyNoFile(): void
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientcert.pem';
        $wsk->addPublicKey(XmlSecurityKey::RSA_SHA1, file_get_contents($filename), false);

        $this->assertTrue($wsk->hasPublicKey());
        $this->assertInstanceOf(XmlSecurityKey::class, $wsk->getPublicKey());
    }
}
