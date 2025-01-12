<?php

namespace src\Neo4jBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Neo4jBundleExtension extends Extension
{
   public function load(array $configs, ContainerBuilder $container): void
   {
       $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
       $loader->load('services.yaml');
   }
}
