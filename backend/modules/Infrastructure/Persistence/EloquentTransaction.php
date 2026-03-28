<?php

namespace Modules\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;

final readonly class EloquentTransaction implements TransactionInterface
{
    public function execute(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
