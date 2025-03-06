<?php

namespace IntelligentIntern\Neo4jBundle\Service;

use App\Contract\GraphDBServiceInterface;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Authentication\Authenticate;
use App\Service\VaultService;
use App\Factory\LogServiceFactory;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Neo4jService implements GraphDBServiceInterface
{
    private ClientInterface $client;
    private $logger;

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function __construct(
        private readonly VaultService $vaultService,
        private readonly LogServiceFactory $logServiceFactory
    ) {
        $this->logger = $this->logServiceFactory->create();

        $neo4jConfig = $this->vaultService->fetchSecret('secret/data/data/neo4j');

        $neo4jUrl = $neo4jConfig['url'] ?? throw new \RuntimeException('NEO4J_URL nicht gefunden in Vault.');
        $username = $neo4jConfig['username'] ?? throw new \RuntimeException('NEO4J_USERNAME nicht gefunden in Vault.');
        $password = $neo4jConfig['password'] ?? throw new \RuntimeException('NEO4J_PASSWORD nicht gefunden in Vault.');

        $this->client = ClientBuilder::create()
            ->withDriver('neo4j', $neo4jUrl, Authenticate::basic($username, $password))
            ->build();
    }

    public function supports(string $provider): bool
    {
        return strtolower($provider) === 'neo4j';
    }

    /**
     * @param string $label
     * @param array $properties
     * @return array
     */
    public function createNode(string $label, array $properties): array
    {
        $this->logger->info(sprintf('Erstelle Node mit Label "%s".', $label));
        $query = sprintf('CREATE (n:%s $props) RETURN n', $label);
        $params = ['props' => $properties];
        $result = $this->client->run($query, $params);
        $node = $result->first()->get('n');

        return $node->toArray();
    }

    /**
     * @param string $label
     * @param string|int $id
     * @return bool
     */
    public function deleteNodeById(string $label, string|int $id): bool
    {
        $this->logger->info(sprintf('Lösche Node mit Label "%s" und ID "%s".', $label, $id));
        $query = sprintf('MATCH (n:%s {id: $id}) DETACH DELETE n RETURN count(n) AS deletedCount', $label);
        $params = ['id' => $id];
        $result = $this->client->run($query, $params);
        $record = $result->first();
        $deletedCount = $record->get('deletedCount');

        return $deletedCount > 0;
    }
}
