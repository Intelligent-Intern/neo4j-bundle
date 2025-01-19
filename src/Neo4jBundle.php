<?php

namespace IntelligentIntern\Neo4jBundle;

use IntelligentIntern\Neo4jBundle\DependencyInjection\Compiler\GraphDBServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class Neo4jBundle extends AbstractBundle
{
   public function build(ContainerBuilder $container): void
   {
       parent::build($container);
       $container->addCompilerPass(new GraphDBServiceCompilerPass());
   }
}
