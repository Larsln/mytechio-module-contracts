<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

/**
 * What a caller hands in to issue a public link. `kind` names the
 * {@see PublicLinkResolver} that turns the link into its target when it is
 * followed (e.g. `stripe.mandate`); `subjectType`/`subjectId` name the thing
 * the link is about (e.g. `contact`/`17`), so the links of a subject can be
 * revoked together; `payload` carries what the resolver needs and never
 * personal data that must not leave the core.
 */
final readonly class NewPublicLink
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $kind,
        public string $subjectType,
        public string $subjectId,
        public array $payload = [],
        public ?\DateTimeImmutable $expiresAt = null,
        public bool $singleUse = false,
        public ?int $createdBy = null,
    ) {}
}
