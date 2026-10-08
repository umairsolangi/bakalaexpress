<?php

namespace App\Support\Api;

/**
 * Hardcoded locations copied from HomeController.
 *
 * NOTE: These values duplicate HomeController ($sectorOptions and $nearAreaOptions)
 * and must be moved to the database later.
 * Copied from: App\Http\Controllers\HomeController@showHomePage (lines 59-68)
 */
class LocationOptions
{
    public const SECTORS = ['4A', '4B', '4C'];

    public const NEAR_AREAS = [
        'ABC Swimming Pool',
        'Tajli Noor Masjid',
        'Lahori Hotel',
        'Ali Chowk',
        'Family Park',
        'Carido Hospital',
        'Rubi Mor',
    ];

    public static function sectors(): array
    {
        return self::SECTORS;
    }

    public static function nearAreas(): array
    {
        return self::NEAR_AREAS;
    }
}
