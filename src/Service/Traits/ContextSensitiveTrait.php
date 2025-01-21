<?php

namespace IntelligentIntern\Neo4jBundle\Service\Traits;
trait ContextSensitiveTrait
{
    public function getRelevantEdges(float $threshold, string $weightType = 'priorityWeight'): array
    {
        $query = "
            MATCH ()-[r]->()
            WHERE coalesce(r.\$weightType, 0) > \$threshold
            RETURN id(r) AS id, r
        ";
        $result = $this->client->run($query, [
            'threshold' => $threshold,
            'weightType' => $weightType,
        ]);

        return array_map(fn($record) => [
            'id' => $record->get('id'),
            'properties' => $record->get('r'),
        ], $result);
    }
}
