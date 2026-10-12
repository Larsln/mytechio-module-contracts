<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Audit\MaintenanceLog;

/**
 * Test double for `MaintenanceLog`: keeps the appended entries in memory
 * (`entries()`, oldest first).
 */
final class FakeMaintenanceLog implements MaintenanceLog
{
    /**
     * @var list<array{actor: ActorRef, action: string, subject_type: ?string, subject_id: int|string|null, context: array<string, mixed>}>
     */
    private array $entries = [];

    public function record(ActorRef $actor, string $action, ?string $subjectType = null, int|string|null $subjectId = null, array $context = []): void
    {
        $this->entries[] = [
            'actor' => $actor,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'context' => $context,
        ];
    }

    /**
     * @return list<array{actor: ActorRef, action: string, subject_type: ?string, subject_id: int|string|null, context: array<string, mixed>}>
     */
    public function entries(): array
    {
        return $this->entries;
    }
}
