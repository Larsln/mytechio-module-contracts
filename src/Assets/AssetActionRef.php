<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

use MyTechIO\Contracts\ContractException;

/**
 * Result of an `AssetSource` action (autorenew, owner change, transfer-out).
 *
 * `state` is `"queued"` when the source processes the action asynchronously
 * (`jobReference` identifies the job), `"done"` when it is already applied,
 * `"unsupported"` when the source cannot perform it.
 */
final readonly class AssetActionRef
{
    /**
     * @param  'queued'|'done'|'unsupported'  $state
     */
    public function __construct(
        public string $sourceKey,
        public string $externalId,
        public string $action,
        public ?string $jobReference,
        public string $state,
    ) {
        if (! in_array($state, ['queued', 'done', 'unsupported'], true)) {
            throw new ContractException("state must be 'queued', 'done' or 'unsupported', got \"{$state}\".");
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'source_key' => $this->sourceKey,
            'external_id' => $this->externalId,
            'action' => $this->action,
            'job_reference' => $this->jobReference,
            'state' => $this->state,
        ];
    }
}
