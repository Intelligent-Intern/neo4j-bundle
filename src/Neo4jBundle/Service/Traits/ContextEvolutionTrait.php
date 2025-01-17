<?php

namespace IntelligentIntern\Neo4jBundle\Service\Traits;

trait ContextEvolutionTrait
{
    public function adjustEdgeWeight(string $edgeId, float $delta, string $weightType = 'normalWeight'): void
    {
        $query = "
            MATCH ()-[r]->()
            WHERE id(r) = \$edgeId
            SET r.\$weightType = coalesce(r.\$weightType, 0) + \$delta
        ";
        $this->client->run($query, [
            'edgeId' => (int)$edgeId,
            'weightType' => $weightType,
            'delta' => $delta,
        ]);
    }

    public function setPriorityWeightForMood(string $mood, float $weightIncrease): void
    {
        $query = "
            MATCH ()-[r]->()
            WHERE r.mood = \$mood
            SET r.priorityWeight = coalesce(r.priorityWeight, 0) + \$weightIncrease
        ";
        $this->client->run($query, [
            'mood' => $mood,
            'weightIncrease' => $weightIncrease,
        ]);
    }

    public function resetPriorityWeights(): void
    {
        $query = "
            MATCH ()-[r]->()
            SET r.priorityWeight = 0
        ";
        $this->client->run($query);
    }

    public function decayAllWeights(string $weightType, float $decayFactor): void
    {
        $query = "
            MATCH ()-[r]->()
            SET r.\$weightType = coalesce(r.\$weightType, 0) * \$decayFactor
        ";
        $this->client->run($query, [
            'weightType' => $weightType,
            'decayFactor' => $decayFactor,
        ]);
    }
}
