<?php

namespace IntelligentIntern;

use IntelligentIntern\DependencyInjection\Compiler\GraphDBServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Neo4jBundle extends Bundle
{
   public function build(ContainerBuilder $container): void
   {
       parent::build($container);
       $container->addCompilerPass(new GraphDBServiceCompilerPass());
   }
}
