<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Options for `IncomingDocuments::ingest()`.
 *
 * `metadata` is passed through 1:1 to the core storage — a flag like the
 * former `skip_notify` is no longer provided here: whether a module skips
 * its own push (e.g. to Paperless) for a document it just pulled itself is
 * decided by the module based on its own data, not through this contract.
 */
final readonly class IngestOptions
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public ?int $actorId = null,
        public array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'actor_id' => $this->actorId,
            'metadata' => $this->metadata,
        ];
    }
}
