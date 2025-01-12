<?php

namespace src\Neo4jBundle\Service\Traits;

use GraphAware\Neo4j\Client\Client;

trait NodeOperationsTrait
{
    public function createNode(array $properties): array
    {
        $query = "CREATE (n {props}) RETURN id(n) AS id, properties(n) AS props";
        $result = $this->client->run($query, ['props' => $properties]);

        $record = $result->getRecord();
        return ['id' => $record->get('id'), 'properties' => $record->get('props')];
    }

    public function findNodeById(string $id): ?array
    {
        $query = "MATCH (n) WHERE id(n) = $id RETURN properties(n) AS props";
        $result = $this->client->run($query, ['id' => (int)$id]);

        if ($result->size() === 0) {
            return null;
        }

        $record = $result->getRecord();
        return ['id' => $id, 'properties' => $record->get('props')];
    }

    public function updateNode(array $node, array $newProperties): array
    {
        $query = "MATCH (n) WHERE id(n) = $id SET n += $props RETURN properties(n) AS updatedProps";
        $result = $this->client->run($query, ['id' => (int)$node['id'], 'props' => $newProperties]);

        $record = $result->getRecord();
        return ['id' => $node['id'], 'properties' => $record->get('updatedProps')];
    }

    public function deleteNode(array $node, bool $cascade = false): void
    {
        $query = $cascade ? "MATCH (n)-[r*]->() WHERE id(n) = $id DETACH DELETE n"
                            : "MATCH (n) WHERE id(n) = $id DELETE n";
        $this->client->run($query, ['id' => (int)$node['id']]);
    }
}
