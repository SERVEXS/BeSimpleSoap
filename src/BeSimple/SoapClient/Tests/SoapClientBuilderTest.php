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

namespace BeSimple\SoapClient\Tests;

use BeSimple\SoapClient\SoapClientBuilder;
use PHPUnit\Framework\TestCase;

class SoapClientBuilderTest extends TestCase
{
    private array $defaultOptions = [
        'features' => 0,
        'classmap' => [],
        'typemap' => [],
    ];

    public function testContruct(): void
    {
        $options = $this
            ->getSoapBuilder()
            ->getSoapOptions()
        ;

        $this->assertEquals($this->mergeOptions([]), $options);
    }

    public function testWithTrace(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withTrace();
        $this->assertEquals($this->mergeOptions(['trace' => true]), $builder->getSoapOptions());

        $builder->withTrace(false);
        $this->assertEquals($this->mergeOptions(['trace' => false]), $builder->getSoapOptions());
    }

    public function testWithExceptions(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withExceptions();
        $this->assertEquals($this->mergeOptions(['exceptions' => true]), $builder->getSoapOptions());

        $builder->withExceptions(false);
        $this->assertEquals($this->mergeOptions(['exceptions' => false]), $builder->getSoapOptions());
    }

    public function testWithUserAgent(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withUserAgent('BeSimpleSoap Test');
        $this->assertEquals($this->mergeOptions(['user_agent' => 'BeSimpleSoap Test']), $builder->getSoapOptions());
    }

    public function testWithCompression(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withCompressionGzip();
        $this->assertEquals($this->mergeOptions(['compression' => \SOAP_COMPRESSION_ACCEPT | \SOAP_COMPRESSION_GZIP]), $builder->getSoapOptions());

        $builder->withCompressionDeflate();
        $this->assertEquals($this->mergeOptions(['compression' => \SOAP_COMPRESSION_ACCEPT | \SOAP_COMPRESSION_DEFLATE]), $builder->getSoapOptions());
    }

    public function testWithAuthentication(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withDigestAuthentication(__DIR__ . '/Fixtures/cert.pem', 'foobar');
        $this->assertEquals($this->mergeOptions(['authentication' => \SOAP_AUTHENTICATION_DIGEST, 'local_cert' => __DIR__ . '/Fixtures/cert.pem', 'passphrase' => 'foobar']), $builder->getSoapOptions());

        $builder->withDigestAuthentication(__DIR__ . '/Fixtures/cert.pem');
        $this->assertEquals($this->mergeOptions(['authentication' => \SOAP_AUTHENTICATION_DIGEST, 'local_cert' => __DIR__ . '/Fixtures/cert.pem']), $builder->getSoapOptions());

        $builder->withBasicAuthentication('foo', 'bar');
        $this->assertEquals($this->mergeOptions(['authentication' => \SOAP_AUTHENTICATION_BASIC, 'login' => 'foo', 'password' => 'bar']), $builder->getSoapOptions());
    }

    public function testWithProxy(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withProxy('localhost', 8080);
        $this->assertEquals($this->mergeOptions(['proxy_host' => 'localhost', 'proxy_port' => 8080]), $builder->getSoapOptions());

        $builder->withProxy('127.0.0.1', 8585, 'foo', 'bar');
        $this->assertEquals($this->mergeOptions(['proxy_host' => '127.0.0.1', 'proxy_port' => 8585, 'proxy_login' => 'foo', 'proxy_password' => 'bar']), $builder->getSoapOptions());

        $builder->withProxy('127.0.0.1', 8585, 'foo', 'bar', \CURLAUTH_BASIC);
        $this->assertEquals($this->mergeOptions(['proxy_host' => '127.0.0.1', 'proxy_port' => 8585, 'proxy_login' => 'foo', 'proxy_password' => 'bar', 'proxy_auth' => \CURLAUTH_BASIC]), $builder->getSoapOptions());

        $builder->withProxy('127.0.0.1', 8585, 'foo', 'bar', \CURLAUTH_NTLM);
        $this->assertEquals($this->mergeOptions(['proxy_host' => '127.0.0.1', 'proxy_port' => 8585, 'proxy_login' => 'foo', 'proxy_password' => 'bar', 'proxy_auth' => \CURLAUTH_NTLM]), $builder->getSoapOptions());

        try {
            $builder->withProxy('127.0.0.1', 8585, 'foo', 'bar', -100);

            $this->fail('An expected exception has not been raised.');
        } catch (\Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }
    }

    public function testCreateWithDefaults(): void
    {
        $builder = SoapClientBuilder::createWithDefaults();

        $this->assertInstanceOf(SoapClientBuilder::class, $builder);

        $this->assertEquals($this->mergeOptions(['soap_version' => \SOAP_1_2, 'encoding' => 'UTF-8', 'features' => \SOAP_SINGLE_ELEMENT_ARRAYS, 'user_agent' => 'BeSimpleSoap']), $builder->getSoapOptions());
    }

    private function getSoapBuilder(): SoapClientBuilder
    {
        return new SoapClientBuilder();
    }

    private function mergeOptions(array $options): array
    {
        return array_merge($this->defaultOptions, $options);
    }
}
