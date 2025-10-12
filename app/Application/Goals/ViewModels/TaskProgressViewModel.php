<?php

namespace App\Application\Goals\ViewModels;

use App\Domain\Tasks\ValueObjects\TaskSnapshot;

final class TaskProgressViewModel
{
    public function __construct(
        private readonly string $id,
        private readonly string $title,
        private readonly ?string $status,
        private readonly ?string $activeSince,
        private readonly int $accumulatedSeconds,
        private readonly ?string $accumulatedHuman,
    ) {}

    public static function fromSnapshot(TaskSnapshot $snapshot): self
    {
        return new self(
            id: $snapshot->id,
            title: $snapshot->title,
            status: $snapshot->status,
            activeSince: $snapshot->activeSince,
            accumulatedSeconds: $snapshot->accumulatedSeconds,
            accumulatedHuman: $snapshot->accumulatedHuman,
        );
    }

    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'title'  => $this->title,
            'status' => $this->status,
            'active_since' => $this->activeSince,
            'accumulated_seconds' => $this->accumulatedSeconds,
            'accumulated_human' => $this->accumulatedHuman,
        ];
    }
}
