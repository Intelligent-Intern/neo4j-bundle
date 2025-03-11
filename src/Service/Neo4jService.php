<?php

namespace IntelligentIntern\Neo4jBundle\Service;

use App\Contract\GraphDBServiceInterface;
use App\Contract\LogServiceInterface;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Authentication\Authenticate;
use App\Service\VaultService;
use App\Factory\LogServiceFactory;
use Laudis\Neo4j\Types\CypherList;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Neo4jService implements GraphDBServiceInterface
{
    private ClientInterface $client;
    private LogServiceInterface $logger;

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
        $neo4jUrl = $neo4jConfig['bolt_address'] ?? throw new \RuntimeException("NEO4J_URL not found in Vault.");
        $auth = $neo4jConfig['auth'] ?? throw new \RuntimeException("NEO4J_AUTH not found in Vault.");
        [$username, $password] = explode('/', $auth, 2);
        $this->client = ClientBuilder::create()
            ->withDriver('neo4j', $neo4jUrl, Authenticate::basic($username, $password))
            ->build();
        $this->logger->info("Neo4j client initialized with URL: " . $neo4jUrl);
    }

    /**
     * @param string $provider
     * @return bool
     */
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
        $query = sprintf("CREATE (n:%s $props) RETURN n", $label);
        $params = ['props' => $properties];
        $result = $this->client->run($query, $params);
        if ($result->isEmpty()) {
            return [];
        }
        $record = $result->first();
        $node = $record->get('n')->toArray();
        $node['id'] = $properties['id'] ?? ($node['id'] ?? '');
        $node['name'] = $properties['name'] ?? ($node['name'] ?? '');
        return $node;
    }

    /**
     * @param string $label
     * @param string|int $id
     * @return bool
     */
    public function deleteNodeById(string $label, string|int $id): bool
    {
        $query = sprintf("MATCH (n:%s {id: \$id}) DETACH DELETE n RETURN count(n) AS deletedCount", $label);
        $params = ['id' => $id];
        $result = $this->client->run($query, $params);
        if ($result->isEmpty()) {
            return false;
        }
        $record = $result->first();
        $deletedCount = $record->get('deletedCount');
        return $deletedCount > 0;
    }

    /**
     * @param string $fromLabel
     * @param string $fromId
     * @param string $relationship
     * @param string $toLabel
     * @param string $toId
     * @return void
     */
    public function createRelationship(string $fromLabel, string $fromId, string $relationship, string $toLabel, string $toId): void
    {
        $query = sprintf(
            "MATCH (a:%s {id: \$startId}), (b:%s {id: \$endId}) CREATE (a)-[:%s]->(b)",
            $fromLabel,
            $toLabel,
            $relationship
        );
        $params = ['startId' => $fromId, 'endId' => $toId];
        $this->client->run($query, $params);
    }

    /**
     * @param string $query
     * @param array $params
     * @return CypherList
     */
    public function runQuery(string $query, array $params = []): CypherList
    {
        $this->logger->info("Running query: " . $query);
        if (!empty($params)) {
            $this->logger->debug("Query parameters: " . json_encode($params));
        }
        $result = $this->client->run($query, $params);
        $this->logger->info("Query executed successfully.");
        return $result;
    }

    /**
     * @param string $filename
     * @return array
     */
    public function createOrUpdateMetaNode(string $filename): array
    {
        $query = 'MERGE (m:ASTMeta {filename: $filename})
                  ON CREATE SET m.id = $id, m.created = timestamp()
                  ON MATCH SET m.updated = timestamp()
                  RETURN m, m.id AS id';
        $params = ['filename' => $filename, 'id' => uniqid('meta_')];
        $result = $this->client->run($query, $params);
        if ($result->isEmpty()) {
            return [];
        }
        $record = $result->first();
        $node = $record->get('m')->toArray();
        $node['id'] = $record->get('id') ?? ($params['id']);
        $node['name'] = $node['name'] ?? $filename;
        return $node;
    }

    /**
     * @param string|int $id
     * @param array $attributes
     * @return array
     */
    public function updateNode(string|int $id, array $attributes): array
    {
        $query = "MATCH (n {id: \$id}) SET n += \$props RETURN n, n.id AS id";
        $params = ['id' => $id, 'props' => $attributes];
        $result = $this->client->run($query, $params);
        if ($result->isEmpty()) {
            return [];
        }
        $record = $result->first();
        $node = $record->get('n')->toArray();
        $node['id'] = $record->get('id') ?? ($attributes['id'] ?? $id);
        $node['name'] = $node['name'] ?? ($attributes['name'] ?? '');
        return $node;
    }

    /**
     * @param string $fromLabel
     * @param string $fromId
     * @param string $relationship
     * @param string $toLabel
     * @param string $toId
     * @return bool
     */
    public function relationshipExists(string $fromLabel, string $fromId, string $relationship, string $toLabel, string $toId): bool
    {
        $query = sprintf(
            "MATCH (a:%s {id: \$startId})-[r:%s]->(b:%s {id: \$endId}) RETURN r LIMIT 1",
            $fromLabel,
            $relationship,
            $toLabel
        );
        $params = ['startId' => $fromId, 'endId' => $toId];
        $result = $this->client->run($query, $params);
        return !$result->isEmpty();
    }
}