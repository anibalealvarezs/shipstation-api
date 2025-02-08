<?php

namespace Anibalealvarezs\ShipStationApi\Enums;

enum OrderStatus: string
{
    case awaiting_payment = 'awaiting_payment';
    case awaiting_shipment = 'awaiting_shipment';
    case pending_fulfillment = 'pending_fulfillment';
    case shipped = 'shipped';
    case on_hold = 'on_hold';
    case cancelled = 'cancelled';
    case rejected_fulfillment = 'rejected_fulfillment';

    public static function getValues(): array
    {
        return [
            self::awaiting_payment,
            self::awaiting_shipment,
            self::pending_fulfillment,
            self::shipped,
            self::on_hold,
            self::cancelled,
            self::rejected_fulfillment,
        ];
    }
}
