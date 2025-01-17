<?php

namespace src\Neo4jBundle\Service\Traits;

use Laudis\Neo4j\Contracts\ClientInterface;

trait EdgeOperationsTrait
{
    private ClientInterface $client;

    public function createEdge(array $fromNode, array $toNode, string $type, array $properties = []): array
    {
        $query = "
            MATCH (a), (b)
            WHERE id(a) = \$fromId AND id(b) = \$toId
            CREATE (a)-[r:$type \$props]->(b)
            RETURN id(r) AS id, type(r) AS type, properties(r) AS props
        ";

        $result = $this->client->run($query, [
            'fromId' => (int)$fromNode['id'],
            'toId' => (int)$toNode['id'],
            'props' => $properties,
        ]);

        $record = $result->first();
        return [
            'id' => $record->get('id'),
            'type' => $record->get('type'),
            'properties' => $record->get('props'),
        ];
    }

    public function deleteEdge(array $edge): void
    {
        $query = "MATCH ()-[r]->() WHERE id(r) = \$id DELETE r";
        $this->client->run($query, ['id' => (int)$edge['id']]);
    }

    public function getNeighbors(array $node, string $relationshipType = null): array
    {
        $relTypeQuery = $relationshipType ? "[:$relationshipType]" : "";
        $query = "
            MATCH (n)-$relTypeQuery-(neighbor)
            WHERE id(n) = \$id
            RETURN id(neighbor) AS id, properties(neighbor) AS props
        ";

        $result = $this->client->run($query, [
            'id' => (int)$node['id']
        ]);

        return array_map(fn($record) => [
            'id' => $record->get('id'),
            'properties' => $record->get('props')
        ], $result);
    }
}
