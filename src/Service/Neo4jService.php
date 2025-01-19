<?php
namespace IntelligentIntern\Neo4jBundle\Service;

use App\Factory\LogServiceFactory;
use App\Interface\GraphDBServiceInterface;
use App\Service\VaultService;
use IntelligentIntern\Neo4jBundle\Service\Traits\ContextEvolutionTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\ContextSensitiveTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\EdgeOperationsTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\GraphAnalysisTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\NodeOperationsTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\QueryExecutionTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\SubgraphOperationsTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\TransactionOperationsTrait;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Interface\LogServiceInterface;

class Neo4jService implements GraphDBServiceInterface
{
    private \Laudis\Neo4j\Contracts\ClientInterface $client;
    private ?LogServiceInterface $logger = null;
    private ?VaultService $vaultService = null;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __construct(VaultService $vaultService, LogServiceFactory $logServiceFactory)
    {
        $this->logger = $this->logServiceFactory->create();

        $neo4jConfig = $vaultService->fetchSecret('secret/data/data/neo4j');

        $neo4jUrl = $neo4jConfig['url'] ?? throw new \RuntimeException('NEO4J_URL not found in Vault.');
        $username = $neo4jConfig['username'] ?? throw new \RuntimeException('NEO4J_USERNAME not found in Vault.');
        $password = $neo4jConfig['password'] ?? throw new \RuntimeException('NEO4J_PASSWORD not found in Vault.');

        $this->client = ClientBuilder::create()
            ->withDriver('neo4j', $neo4jUrl, Authenticate::basic($username, $password))
            ->build();
    }

    use NodeOperationsTrait;
    use EdgeOperationsTrait;
    use SubgraphOperationsTrait;
    use QueryExecutionTrait;
    use GraphAnalysisTrait;
    use TransactionOperationsTrait;
    use ContextEvolutionTrait;
    use ContextSensitiveTrait;

    public function supports(string $provider): bool
    {
        return strtolower($provider) === 'neo4j';
    }

}
