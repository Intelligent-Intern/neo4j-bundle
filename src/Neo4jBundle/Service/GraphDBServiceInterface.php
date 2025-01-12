<?php

namespace src\Neo4jBundle\Service;

use src\Neo4jBundle\Service\Traits\EdgeOperationsTrait;
use src\Neo4jBundle\Service\Traits\GraphAnalysisTrait;
use src\Neo4jBundle\Service\Traits\NodeOperationsTrait;
use src\Neo4jBundle\Service\Traits\QueryExecutionTrait;
use src\Neo4jBundle\Service\Traits\SubgraphOperationsTrait;

interface GraphDBServiceInterface
{
   use NodeOperationsTrait;
   use EdgeOperationsTrait;
   use SubgraphOperationsTrait;
   use QueryExecutionTrait;
   use GraphAnalysisTrait;
}
