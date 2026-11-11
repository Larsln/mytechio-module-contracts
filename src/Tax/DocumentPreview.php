<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * Tax preview of a document before it exists (since 2.17): what would the
 * core decide and calculate for these lines if they were an order of this
 * contact on this date? The answer is computed by the same rules and the
 * same arithmetic as a saved order, so a module can show a customer the
 * amounts that will later stand on the order — it never calculates tax
 * itself.
 *
 * Nothing is stored or logged. The core implements this contract.
 */
interface DocumentPreview
{
    /**
     * @param  string  $date  document date, `Y-m-d`
     * @param  list<PreviewLine>  $lines
     *
     * @throws DocumentPreviewException when the contact, a category or an article is unknown or a line has no tax rate
     */
    public function preview(int $contactId, string $date, array $lines): DocumentPreviewData;
}
