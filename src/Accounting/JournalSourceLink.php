<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * Link from a journal entry back to the object that caused it: the label shown
 * on the journal pages (for example the voucher number) and the core-relative
 * URL of the object's page.
 */
final readonly class JournalSourceLink
{
    public function __construct(
        public string $label,
        public string $url,
    ) {
        if (trim($label) === '') {
            throw new ContractException('label must not be empty.');
        }

        if (! str_starts_with($url, '/')) {
            throw new ContractException('url must be a path relative to the core.');
        }
    }

    /**
     * @return array{label: string, url: string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'url' => $this->url,
        ];
    }
}
