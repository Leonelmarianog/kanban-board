<?php

namespace Modules\Infrastructure\Persistence;

interface TransactionInterface
{
    /**
     * Execute a callback within a database transaction.
     * Automatically commits on success, rolls back on failure.
     */
    public function execute(callable $callback): mixed;
}
