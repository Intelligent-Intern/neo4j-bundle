<?php

namespace IntelligentIntern\Service\Traits;

use Laudis\Neo4j\Contracts\ClientInterface;

trait QueryExecutionTrait
{
    private ClientInterface $client;

    public function runCustomQuery(string $cypherQuery, array $parameters = []): array
    {
        $result = $this->client->run($cypherQuery, $parameters);

        return array_map(
            fn($record) => $record->values(),
            $result
        );
    }
}
