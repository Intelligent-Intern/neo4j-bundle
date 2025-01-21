<?php

namespace IntelligentIntern\Neo4jBundle\Service\Traits;

trait QueryExecutionTrait
{
    public function runCustomQuery(string $cypherQuery, array $parameters = []): array
    {
        $result = $this->client->run($cypherQuery, $parameters);

        return array_map(
            fn($record) => $record->values(),
            $result
        );
    }
}
