<?php

namespace src\Neo4jBundle\Service\Traits;

use GraphAware\Neo4j\Client\Client;

trait GraphAnalysisTrait
{
    public function getNodeCount(string $label = null): int
    {
        $labelQuery = $label ? ":$label" : "";
        $query = "MATCH (n$labelQuery) RETURN count(n) AS count";
        $result = $this->client->run($query);

        return $result->getRecord()->get('count');
    }

    public function findPath(array $startNode, array $endNode, array $criteria = []): array
    {
        $criteriaQuery = !empty($criteria) ? "WHERE " . implode(" AND ", $criteria) : "";
        $query = "MATCH p = shortestPath((n)-[*]-(m)) WHERE id(n) = $startId AND id(m) = $endId $criteriaQuery RETURN p";
        $result = $this->client->run($query, ['startId' => (int)$startNode['id'], 'endId' => (int)$endNode['id']]);

        return $result->getRecords();
    }
}
