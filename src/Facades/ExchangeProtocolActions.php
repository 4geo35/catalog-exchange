<?php

namespace GIS\CatalogExchange\Facades;

use GIS\CatalogExchange\Helpers\ExchangeProtocolActionsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string init()
 * @method static string failure(string $details = "")
 * @method static string answer(string $value)
 *
 * @see ExchangeProtocolActionsManager
 */
class ExchangeProtocolActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "exchange-protocol-actions";
    }
}
