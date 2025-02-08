<?php

namespace Anibalealvarezs\ShipStationApi\Enums;

enum OrderSort: string
{
    case order_date = 'OrderDate';
    case modify_date = 'ModifyDate';
    case create_date = 'CreateDate';

    public static function getValues(): array
    {
        return [
            self::order_date,
            self::modify_date,
            self::create_date,
        ];
    }
}
