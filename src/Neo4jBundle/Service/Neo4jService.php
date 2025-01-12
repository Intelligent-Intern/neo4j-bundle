<?php

namespace src\Neo4jBundle\Service;

use GraphAware\Neo4j\Client\ClientBuilder;

class Neo4jService implements GraphDBServiceInterface
{
   private $client;

   public function __construct()
   {
       $neo4jUrl = $_ENV['NEO4J_URL'] ?? throw new \RuntimeException('NEO4J_URL is not set');
       $username = $_ENV['NEO4J_USERNAME'] ?? throw new \RuntimeException('NEO4J_USERNAME is not set');
       $password = $_ENV['NEO4J_PASSWORD'] ?? throw new \RuntimeException('NEO4J_PASSWORD is not set');

       $this->client = ClientBuilder::create()
           ->addConnection('default', "http://$username:$password@$neo4jUrl")
           ->build();
   }
}
