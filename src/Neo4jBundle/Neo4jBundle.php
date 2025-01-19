<?php

namespace IntelligentIntern\Neo4jBundle;

use IntelligentIntern\Neo4jBundle\DependencyInjection\Compiler\GraphDBServiceCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Neo4jBundle extends Bundle
{
   public function build(ContainerBuilder $container): void
   {
       parent::build($container);
       $container->addCompilerPass(new GraphDBServiceCompilerPass());
   }
}
