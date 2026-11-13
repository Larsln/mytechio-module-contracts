<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping\Carriers;

use MyTechIO\Contracts\Shipping\ShipmentAddress;
use MyTechIO\Contracts\Shipping\ShipmentException;

/**
 * A carrier driver that can create a label for the way back (since
 * 2.20). The holder of `shipping` keeps the price per country and the
 * carrier's name for where returns go (`receiverId`), and stores the label.
 */
interface CreatesReturnLabels
{
    /** Whether return labels can be created right now (the access for it is complete). */
    public function returnsReady(): bool;

    /**
     * @param  ShipmentAddress  $sender  the customer who sends the goods back
     * @param  string  $receiverId  what the carrier calls the return address for the sender's country
     *
     * @throws ShipmentException when the address cannot be used, the carrier refuses or cannot be reached
     */
    public function createReturnLabel(ShipmentAddress $sender, string $receiverId, ?string $reference, ?int $weightGrams): ReturnLabelFile;
}
