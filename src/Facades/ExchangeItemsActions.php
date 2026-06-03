<?php

namespace GIS\CatalogExchange\Facades;

use GIS\CatalogExchange\Helpers\ExchangeItemsActionsManager;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void processFile(CmlFileInterface $cmlFile)
 *
 * @see ExchangeItemsActionsManager
 */
class ExchangeItemsActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "exchange-items-actions";
    }
}
