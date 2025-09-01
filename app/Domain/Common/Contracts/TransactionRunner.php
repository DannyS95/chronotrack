<?php

namespace App\Domain\Common\Contracts;

interface TransactionRunner
{
    /**
     * Run the given callable inside a transaction and return its result.
     *
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function run(callable $callback);
}
