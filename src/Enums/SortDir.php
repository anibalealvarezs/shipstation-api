<?php

namespace Anibalealvarezs\ShipStationApi\Enums;

enum SortDir: string
{
    case asc = 'ASC';
    case desc = 'DESC';

    public static function getValues(): array
    {
        return [
            self::asc,
            self::desc,
        ];
    }
}
