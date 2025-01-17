<?php
namespace IntelligentIntern\Neo4jBundle\Service;

use App\Interface\GraphDBServiceInterface;
use App\Service\VaultService;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;
use Psr\Log\LoggerInterface;

class Neo4jService implements GraphDBServiceInterface
{
    private $client;
    private ?LoggerInterface $logger = null;
    private ?VaultService $vaultService = null;

    public function __construct(VaultService $vaultService)
    {
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

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setVaultService(VaultService $vaultService): void
    {
        $this->vaultService = $vaultService;
    }
}
