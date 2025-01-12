<?php

namespace src\Neo4jBundle\Service\Traits;

use GraphAware\Neo4j\Client\Client;

trait QueryExecutionTrait
{
    public function runCustomQuery(string $cypherQuery, array $parameters = []): mixed
    {
        return $this->client->run($cypherQuery, $parameters)->getRecords();
    }
}
