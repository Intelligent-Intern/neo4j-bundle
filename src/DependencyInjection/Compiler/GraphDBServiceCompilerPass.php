<?php

namespace IntelligentIntern\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class GraphDBServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('App\Factory\GraphDBFactory')) {
            return;
        }

        $definition = $container->findDefinition('App\Factory\GraphDBFactory');

        $taggedServices = $container->findTaggedServiceIds('graphdb.strategy');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addStrategy', [new Reference($id)]);
        }
    }
}
