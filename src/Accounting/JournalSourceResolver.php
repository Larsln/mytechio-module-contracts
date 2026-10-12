<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * A module's explanation of the journal entries it posted: where the entry
 * comes from and whether it may be reversed by hand.
 *
 * The core collects implementations via the container tag
 * `mytechio.journal_source_resolvers`
 * (`$this->app->tag([MyResolver::class], 'mytechio.journal_source_resolvers')`)
 * and asks every resolver of an enabled, compatible module for the entries
 * whose `sourceType` it lists, after the core's own cases. Resolvers of a
 * module that is disabled or incompatible are skipped; an entry nobody claims
 * has no source link and can be reversed by hand.
 *
 * Both lookups are reads: they run on the journal pages and must not change
 * anything.
 */
interface JournalSourceResolver
{
    /**
     * The `JournalEntryDraft::$sourceType` values this resolver answers for.
     *
     * @return list<string>
     */
    public function sourceTypes(): array;

    /**
     * The link to the object behind the entry (document or register object),
     * or null when it cannot be resolved.
     */
    public function link(JournalEntryRef $entry): ?JournalSourceLink;

    /**
     * A non-null text (German, shown to the user) means the entry must not be
     * reversed manually and names the workflow to use instead; the core
     * refuses a manual reversal with exactly this text. Null allows the manual
     * reversal.
     */
    public function reversalHint(JournalEntryRef $entry): ?string;
}
