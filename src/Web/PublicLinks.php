<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

/**
 * Public links the website answers on behalf of the core: the mail of a
 * document carries `https://<website>/l/<token>`, the website asks the holder
 * of this contract for the target, and the holder resolves it through the
 * {@see PublicLinkResolver} of the link's kind at that moment.
 *
 * Implemented by the module holding the exclusive capability `public_links`.
 * The core binds a null implementation that reports `available() === false`
 * while no such module is active, so callers fall back to texts without a link.
 */
interface PublicLinks
{
    public function available(): bool;

    /**
     * @throws PublicLinksUnavailableException while no module holds `public_links`
     */
    public function create(NewPublicLink $link): PublicLinkData;

    public function find(string $token): ?PublicLinkData;

    /**
     * Active links of a subject, newest first.
     *
     * @return list<PublicLinkData>
     */
    public function forSubject(string $kind, string $subjectType, string $subjectId): array;

    /** Revokes one link; a revoked link answers "invalid" from then on. No-op for an unknown token. */
    public function revoke(string $token, ?string $reason = null): void;

    /**
     * Revokes every active link of a subject (e.g. after the mandate was given).
     *
     * @return int number of links revoked
     */
    public function revokeForSubject(string $kind, string $subjectType, string $subjectId, ?string $reason = null): int;

    /**
     * Follows a link as the website does: checks status and expiry, asks the
     * kind's resolver, counts the click and uses up a single-use link when the
     * target is a redirect. Unknown tokens answer `invalid` with reason `not_found`.
     */
    public function follow(string $token): LinkTarget;
}
