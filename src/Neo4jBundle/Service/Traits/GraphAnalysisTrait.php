<?php

namespace IntelligentIntern\Neo4jBundle\Service\Traits;

use Laudis\Neo4j\Contracts\ClientInterface;

trait GraphAnalysisTrait
{
    private ClientInterface $client;

    public function getNodeCount(string $label = null): int
    {
        $labelQuery = $label ? ":$label" : "";
        $query = "MATCH (n$labelQuery) RETURN count(n) AS count";

        $result = $this->client->run($query);
        return $result->first()->get('count');
    }

    public function findPath(array $startNode, array $endNode, array $criteria = []): array
    {
        $criteriaQuery = !empty($criteria) ? " AND " . implode(" AND ", $criteria) : "";
        $query = "
            MATCH p = shortestPath((n)-[*]-(m))
            WHERE id(n) = \$startId AND id(m) = \$endId $criteriaQuery
            RETURN p
        ";

        $result = $this->client->run($query, [
            'startId' => (int)$startNode['id'],
            'endId' => (int)$endNode['id']
        ]);

        return array_map(fn($record) => $record->get('p'), $result);
    }
}
