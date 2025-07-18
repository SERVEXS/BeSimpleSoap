<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds tagged besimple.soap.definition.loader services to ebservice.definition.resolver service
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class WebServiceResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition('besimple.soap.definition.loader.resolver')) {
            return;
        }

        $definition = $container->getDefinition('besimple.soap.definition.loader.resolver');

        foreach ($container->findTaggedServiceIds('besimple.soap.definition.loader') as $id => $attributes) {
            $definition->addMethodCall('addLoader', [new Reference($id)]);
        }
    }
}
