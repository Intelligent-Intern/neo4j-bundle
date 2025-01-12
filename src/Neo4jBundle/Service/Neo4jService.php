<?php

namespace IntelligentIntern\Neo4jBundle\Service;

use Neoxygen\NeoClient\ClientBuilder;

class Neo4jService implements GraphDBServiceInterface
{
    private $client;

    public function __construct()
    {
        $neo4jUrl = $_ENV['NEO4J_URL'] ?? throw new \RuntimeException('NEO4J_URL is not set');
        $username = $_ENV['NEO4J_USERNAME'] ?? throw new \RuntimeException('NEO4J_USERNAME is not set');
        $password = $_ENV['NEO4J_PASSWORD'] ?? throw new \RuntimeException('NEO4J_PASSWORD is not set');

        $this->client = ClientBuilder::create()
            ->withCredentials($neo4jUrl, $username, $password)
            ->build();
    }

    public function createNode(array $properties): void
    {
        $this->client->run('CREATE (n:Node {props})', ['props' => $properties]);
    }
}
