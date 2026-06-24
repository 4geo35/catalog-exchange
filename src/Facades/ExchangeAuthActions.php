<?php

namespace GIS\CatalogExchange\Facades;

use GIS\CatalogExchange\Helpers\ExchangeAuthActionsManager;
use GIS\CatalogExchange\Interfaces\CmlInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string modeCheckAuth()
 * @method static bool checkRequestUser()
 * @method static CmlInterface|string getUserCookie()
 *
 * @see ExchangeAuthActionsManager
 */
class ExchangeAuthActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "exchange-auth-actions";
    }
}
