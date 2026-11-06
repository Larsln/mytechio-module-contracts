<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

/**
 * Turns a followed public link of one `kind` into its target, container tag
 * `mytechio.public_link_resolvers`. The holder of `public_links` calls it
 * when the website asks for a link; the resolver decides at that moment
 * (e.g. creates the Stripe mandate page, or answers "already done").
 */
interface PublicLinkResolver
{
    /** The kind this resolver answers, e.g. `stripe.mandate`; one resolver per kind. */
    public function kind(): string;

    /** German name for the link overview, e.g. `SEPA-Mandat (Stripe)`. */
    public function label(): string;

    public function resolve(PublicLinkData $link): LinkTarget;
}
