<?php

namespace App\Enums;

enum StockTransferStatus: int
{
    case Pending = 1;
    case In_Transit = 2;
    case Completed = 3;
    case Cancelled = 4;

    /**
     * Get the human-readable status for the Stock Transfer Status enum.
     *
     * @return string The status of the Stock Transfer, such as 'Pending' or 'In_Transit'.
     */
    public function asAString(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::In_Transit => 'In Transit',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
