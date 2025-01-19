<?php

namespace IntelligentIntern\Neo4jBundle\Service\Traits;

use Laudis\Neo4j\Contracts\ClientInterface;

trait SubgraphOperationsTrait
{
    private ClientInterface $client;

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
        // Beispielhafte Implementierung eines Vergleichs
        return $this->similarityScore($subgraphA, $subgraphB);
    }

    private function similarityScore(array $subgraphA, array $subgraphB): float
    {
        // Berechnet die Ähnlichkeit zwischen zwei Subgraphen
        $commonElements = array_intersect($subgraphA, $subgraphB);
        $totalElements = array_unique(array_merge($subgraphA, $subgraphB));

        return count($commonElements) / max(1, count($totalElements));
    }
}
