<?php

namespace IntelligentIntern\Neo4jBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use src\Neo4jBundle\DependencyInjection\Compiler\GraphDBServiceCompilerPass;

class Neo4jBundle extends Bundle
{
   public function build(ContainerBuilder $container): void
   {
       parent::build($container);
       $container->addCompilerPass(new GraphDBServiceCompilerPass());
   }
}
