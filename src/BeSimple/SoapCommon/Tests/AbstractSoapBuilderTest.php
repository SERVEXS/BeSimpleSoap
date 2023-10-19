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
use BeSimple\SoapCommon\Classmap;
use BeSimple\SoapCommon\Converter\DateTimeTypeConverter;
use BeSimple\SoapCommon\Converter\DateTypeConverter;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;
use BeSimple\SoapCommon\Tests\Fixtures\SoapBuilder;
use PHPUnit\Framework\TestCase;

class AbstractSoapBuilderTest extends TestCase
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

    public function testWithWsdl(): void
    {
        $builder = $this->getSoapBuilder();
        $this->assertNull($builder->getWsdl());

        $builder->withWsdl('http://myWsdl/?wsdl');
        $this->assertEquals('http://myWsdl/?wsdl', $builder->getWsdl());
    }

    public function testWithSoapVersion(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withSoapVersion11();
        $this->assertEquals($this->mergeOptions(['soap_version' => \SOAP_1_1]), $builder->getSoapOptions());

        $builder->withSoapVersion12();
        $this->assertEquals($this->mergeOptions(['soap_version' => \SOAP_1_2]), $builder->getSoapOptions());
    }

    public function testWithEncoding(): void
    {
        $builder = $this
            ->getSoapBuilder()
            ->withEncoding('ISO 8859-15')
        ;

        $this->assertEquals($this->mergeOptions(['encoding' => 'ISO 8859-15']), $builder->getSoapOptions());
    }

    public function testWithWsdlCache(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withWsdlCache(Cache::TYPE_DISK_MEMORY);
        $this->assertEquals($this->mergeOptions(['cache_wsdl' => Cache::TYPE_DISK_MEMORY]), $builder->getSoapOptions());

        $builder->withWsdlCacheNone();
        $this->assertEquals($this->mergeOptions(['cache_wsdl' => Cache::TYPE_NONE]), $builder->getSoapOptions());

        $builder->withWsdlCacheDisk();
        $this->assertEquals($this->mergeOptions(['cache_wsdl' => Cache::TYPE_DISK]), $builder->getSoapOptions());

        $builder->withWsdlCacheMemory();
        $this->assertEquals($this->mergeOptions(['cache_wsdl' => Cache::TYPE_MEMORY]), $builder->getSoapOptions());

        $builder->withWsdlCacheDiskAndMemory();
        $this->assertEquals($this->mergeOptions(['cache_wsdl' => Cache::TYPE_DISK_MEMORY]), $builder->getSoapOptions());
    }

    public function testWithWsdlCacheBadValue(): void
    {
        $builder = $this->getSoapBuilder();

        $this->expectException('InvalidArgumentException');
        $builder->withWsdlCache('foo');
    }

    public function testWithSingleElementArrays(): void
    {
        $options = $this
            ->getSoapBuilder()
            ->withSingleElementArrays()
            ->getSoapOptions()
        ;

        $this->assertEquals($this->mergeOptions(['features' => \SOAP_SINGLE_ELEMENT_ARRAYS]), $options);
    }

    public function testWithWaitOneWayCalls(): void
    {
        $options = $this
            ->getSoapBuilder()
            ->withWaitOneWayCalls()
            ->getSoapOptions()
        ;

        $this->assertEquals($this->mergeOptions(['features' => \SOAP_WAIT_ONE_WAY_CALLS]), $options);
    }

    public function testWithUseXsiArrayType(): void
    {
        $options = $this
            ->getSoapBuilder()
            ->withUseXsiArrayType()
            ->getSoapOptions()
        ;

        $this->assertEquals($this->mergeOptions(['features' => \SOAP_USE_XSI_ARRAY_TYPE]), $options);
    }

    public function testFeatures(): void
    {
        $builder = $this->getSoapBuilder();
        $features = 0;

        $builder->withSingleElementArrays();
        $features |= \SOAP_SINGLE_ELEMENT_ARRAYS;
        $this->assertEquals($this->mergeOptions(['features' => $features]), $builder->getSoapOptions());

        $builder->withWaitOneWayCalls();
        $features |= \SOAP_WAIT_ONE_WAY_CALLS;
        $this->assertEquals($this->mergeOptions(['features' => $features]), $builder->getSoapOptions());

        $builder->withUseXsiArrayType();
        $features |= \SOAP_USE_XSI_ARRAY_TYPE;
        $this->assertEquals($this->mergeOptions(['features' => $features]), $builder->getSoapOptions());
    }

    public function testWithTypeConverters(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withTypeConverter(new DateTypeConverter());
        $options = $builder->getSoapOptions();

        $this->assertCount(1, $options['typemap']);

        $converters = new TypeConverterCollection();
        $converters->add(new DateTimeTypeConverter());
        $builder->withTypeConverters($converters);
        $options = $builder->getSoapOptions();

        $this->assertCount(2, $options['typemap']);

        $builder->withTypeConverters($converters, false);
        $options = $builder->getSoapOptions();

        $this->assertCount(1, $options['typemap']);
    }

    public function testClassmap(): void
    {
        $builder = $this->getSoapBuilder();

        $builder->withClassMapping('foo', __CLASS__);
        $options = $builder->getSoapOptions();

        $this->assertCount(1, $options['classmap']);

        $classmap = new Classmap();
        $classmap->add('bar', __CLASS__);
        $builder->withClassmap($classmap);
        $options = $builder->getSoapOptions();

        $this->assertCount(2, $options['classmap']);

        $builder->withClassmap($classmap, false);
        $options = $builder->getSoapOptions();

        $this->assertCount(1, $options['classmap']);
    }

    public function testCreateWithDefaults(): void
    {
        $builder = SoapBuilder::createWithDefaults();

        $this->assertInstanceOf(SoapBuilder::class, $builder);

        $this->assertEquals($this->mergeOptions(['soap_version' => \SOAP_1_2, 'encoding' => 'UTF-8', 'features' => \SOAP_SINGLE_ELEMENT_ARRAYS]), $builder->getSoapOptions());
    }

    private function getSoapBuilder(): SoapBuilder
    {
        return new SoapBuilder();
    }

    private function mergeOptions(array $options): array
    {
        return array_merge($this->defaultOptions, $options);
    }
}
