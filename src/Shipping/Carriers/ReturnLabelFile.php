<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping\Carriers;

/**
 * A return label as the carrier made it (since 2.20): the number of the
 * return shipment, the file itself (binary), its MIME type and the public
 * page where the way back can be followed.
 */
final readonly class ReturnLabelFile
{
    public function __construct(
        public string $number,
        public string $content,
        public string $mime = 'application/pdf',
        public ?string $trackingUrl = null,
    ) {}
}
