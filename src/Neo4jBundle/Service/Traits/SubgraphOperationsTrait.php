<?php

namespace src\Neo4jBundle\Service\Traits;

use GraphAware\Neo4j\Client\Client;
use function IntelligentIntern\Neo4jBundle\Service\Traits\similarity_score;

trait SubgraphOperationsTrait
{
    public function extractSubgraph(array $rootNode, int $depth, array $criteria = []): array
    {
        $criteriaQuery = !empty($criteria) ? "WHERE " . implode(" AND ", $criteria) : "";
        $query = "MATCH p = (n)-[*1..$depth]-(m) WHERE id(n) = $id $criteriaQuery RETURN p";
        $result = $this->client->run($query, ['id' => (int)$rootNode['id'], 'depth' => $depth]);

        return $result->getRecords();
    }

    public function compareSubgraphs(array $subgraphA, array $subgraphB): float
    {
        // Placeholder logic for comparison
        return similarity_score($subgraphA, $subgraphB);
    }
}
