<?php

namespace App\Infrastructure\Support;

use App\Domain\Common\Contracts\TransactionRunner;
use Illuminate\Support\Facades\DB;

final class LaravelTransactionRunner implements TransactionRunner
{
    public function run(callable $callback)
    {
        return DB::transaction($callback);
    }
}
