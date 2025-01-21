<?php

namespace IntelligentIntern\Neo4jBundle\Service\Traits;

trait SubgraphOperationsTrait
{
    public function extractSubgraph(array $rootNode, int $depth, array $criteria = []): array
    {
        $criteriaQuery = !empty($criteria) ? " AND " . implode(" AND ", $criteria) : "";
        $query = "
            MATCH p = (n)-[*1..$depth]-(m)
            WHERE id(n) = \$id $criteriaQuery
            RETURN p
        ";
        $result = $this->client->run($query, [
            'id' => (int)$rootNode['id'],
        ]);

        return array_map(
            fn($record) => $record->get('p'),
            $result
        );
    }

    public function compareSubgraphs(array $subgraphA, array $subgraphB): float
    {
        return $this->similarityScore($subgraphA, $subgraphB);
    }

    private function similarityScore(array $subgraphA, array $subgraphB): float
    {
        $commonElements = array_intersect($subgraphA, $subgraphB);
        $totalElements = array_unique(array_merge($subgraphA, $subgraphB));

        return count($commonElements) / max(1, count($totalElements));
    }
}
