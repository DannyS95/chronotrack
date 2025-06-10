<?php

namespace App\Domain\Projects\Contracts;

interface ProjectRepositoryInterface
{
    public function create(array $data);
    public function findById(string $id);
}
