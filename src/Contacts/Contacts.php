<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * Rein lesender Zugriff auf Kontakte des Kerns.
 *
 * Module dürfen Kontakte über diesen Vertrag nachschlagen und durchsuchen,
 * aber nicht anlegen oder ändern — Kontaktstammdaten bleiben ausschließlich
 * Sache des Kerns.
 */
interface Contacts
{
    public function find(int $id): ?ContactData;

    /**
     * @return list<ContactData>
     */
    public function search(string $query, int $limit = 20): array;

    /**
     * @return array<string, string> ISO-2 ⇒ Landesname (deutsch)
     */
    public function countries(): array;
}
