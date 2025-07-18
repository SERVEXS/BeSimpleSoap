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

namespace BeSimple\SoapCommon;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Classmap
{
    /**
     * @var array
     */
    protected $classmap = [];

    /**
     * @return array
     */
    public function all()
    {
        return $this->classmap;
    }

    /**
     * @param string $type
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function get($type)
    {
        if (!$this->has($type)) {
            throw new \InvalidArgumentException(sprintf('The type "%s" does not exists', $type));
        }

        return $this->classmap[$type];
    }

    /**
     * @param string $type
     * @param string $classname
     *
     * @throws \InvalidArgumentException
     */
    public function add($type, $classname): void
    {
        if ($this->has($type)) {
            throw new \InvalidArgumentException(sprintf('The type "%s" already exists', $type));
        }

        $this->classmap[$type] = $classname;
    }

    public function set(array $classmap): void
    {
        $this->classmap = [];

        foreach ($classmap as $type => $classname) {
            $this->add($type, $classname);
        }
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function has($type)
    {
        return isset($this->classmap[$type]);
    }

    public function addClassmap(self $classmap): void
    {
        foreach ($classmap->all() as $type => $classname) {
            $this->add($type, $classname);
        }
    }
}
