<?php

namespace App\Domain\Common\Contracts;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
