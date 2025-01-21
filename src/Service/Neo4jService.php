<?php
namespace IntelligentIntern\Neo4jBundle\Service;

use App\Factory\LogServiceFactory;
use App\Service\VaultService;
use App\Contracts\LogServiceInterface;
use App\Contracts\GraphDBServiceInterface;
use IntelligentIntern\Neo4jBundle\Service\Traits\ContextEvolutionTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\ContextSensitiveTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\EdgeOperationsTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\GraphAnalysisTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\NodeOperationsTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\QueryExecutionTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\SubgraphOperationsTrait;
use IntelligentIntern\Neo4jBundle\Service\Traits\TransactionOperationsTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Contracts\ClientInterface;

class Neo4jService implements GraphDBServiceInterface
{
    private ClientInterface $client;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __construct(
        private readonly VaultService $vaultService,
        private readonly LogServiceFactory $logServiceFactory
    ) {
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
