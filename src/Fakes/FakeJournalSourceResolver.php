<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\JournalEntryRef;
use MyTechIO\Contracts\Accounting\JournalSourceLink;
use MyTechIO\Contracts\Accounting\JournalSourceResolver;

/**
 * Test double for `JournalSourceResolver`: answers for the configured source
 * types, builds the link as `{urlPrefix}/{sourceId}` labelled with the
 * entry's document reference (or the source ID), and returns the configured
 * reversal hint. Every asked entry is recorded in `linkCalls` and
 * `reversalHintCalls`.
 */
final class FakeJournalSourceResolver implements JournalSourceResolver
{
    /**
     * @var list<JournalEntryRef>
     */
    public array $linkCalls = [];

    /**
     * @var list<JournalEntryRef>
     */
    public array $reversalHintCalls = [];

    /**
     * @param  list<string>  $sourceTypes
     */
    public function __construct(
        private array $sourceTypes = ['fake.source'],
        private readonly string $urlPrefix = '/fake',
        private ?string $reversalHint = null,
    ) {}

    public function sourceTypes(): array
    {
        return $this->sourceTypes;
    }

    public function link(JournalEntryRef $entry): ?JournalSourceLink
    {
        $this->linkCalls[] = $entry;

        if ($entry->sourceId === null || ! in_array($entry->sourceType, $this->sourceTypes, true)) {
            return null;
        }

        return new JournalSourceLink($entry->documentReference ?? $entry->sourceId, $this->urlPrefix.'/'.$entry->sourceId);
    }

    public function reversalHint(JournalEntryRef $entry): ?string
    {
        $this->reversalHintCalls[] = $entry;

        return in_array($entry->sourceType, $this->sourceTypes, true) ? $this->reversalHint : null;
    }

    public function withReversalHint(?string $hint): void
    {
        $this->reversalHint = $hint;
    }
}
