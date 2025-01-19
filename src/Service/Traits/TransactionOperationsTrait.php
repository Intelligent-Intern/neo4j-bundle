<?php

namespace IntelligentIntern\Service\Traits;

use Laudis\Neo4j\Contracts\TransactionInterface;
use Psr\Log\LoggerInterface;

trait TransactionOperationsTrait
{
    private LoggerInterface $logger;

    public function startTransaction(): TransactionInterface
    {
        $this->logger->info('Starting a new transaction.');
        return $this->client->beginTransaction();
    }

    /**
     * @throws \Exception
     */
    public function commitTransaction(TransactionInterface $transaction): void
    {
        try {
            $transaction->commit();
            $this->logger->info('Transaction committed successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Failed to commit transaction.', ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function rollbackTransaction(TransactionInterface $transaction): void
    {
        try {
            $transaction->rollback();
            $this->logger->warning('Transaction rolled back successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Failed to rollback transaction.', ['exception' => $e]);
            throw $e;
        }
    }
}
