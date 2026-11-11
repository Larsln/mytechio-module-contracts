<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/** The printable label of a parcel: the file's bytes, its MIME type and a file name. */
final readonly class ShipmentLabel
{
    public function __construct(
        public string $content,
        public string $mimeType,
        public string $filename,
    ) {}
}
