<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Tests;

use BeSimple\SoapCommon\Classmap;
use PHPUnit\Framework\TestCase;

/**
 * UnitTest for \BeSimple\SoapCommon\Classmap.
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ClassmapTest extends TestCase
{
    public function testAll(): void
    {
        $classmap = new Classmap();

        $this->assertSame(array(), $classmap->all());
    }

    public function testAdd(): void
    {
        $classmap = new Classmap();

        $classmap->add('foobar', Classmap::class);

        $this->expectException('InvalidArgumentException');
        $classmap->add('foobar', Classmap::class);
    }

    public function testGet(): void
    {
        $classmap = new Classmap();

        $classmap->add('foobar', Classmap::class);
        $this->assertSame(Classmap::class, $classmap->get('foobar'));

        $this->expectException('InvalidArgumentException');
        $classmap->get('bar');
    }

    public function testSet(): void
    {
        $classmap = new Classmap();

        $classmap->add('foobar', __CLASS__);
        $classmap->add('foo', 'BeSimple\SoapCommon\Tests\Classmap');

        $map = array(
            'foobar' => Classmap::class,
            'barfoo' => __CLASS__,
        );
        $classmap->set($map);

        $this->assertSame($map, $classmap->all());
    }

    public function testAddClassmap(): void
    {
        $classmap1 = new Classmap();
        $classmap2 = new Classmap();

        $classmap2->add('foobar', Classmap::class);
        $classmap1->addClassmap($classmap2);

        $this->assertEquals(array('foobar' => Classmap::class), $classmap1->all());

        $this->expectException('InvalidArgumentException');
        $classmap1->addClassmap($classmap2);
    }
}
