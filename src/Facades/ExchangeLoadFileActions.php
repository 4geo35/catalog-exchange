<?php

namespace GIS\CatalogExchange\Facades;

use GIS\CatalogExchange\Helpers\ExchangeLoadFileActionsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array getAvailableFileTypes()
 * @method static string getFileTypeByName(string $name)
 *
 * @see ExchangeLoadFileActionsManager
 */
class ExchangeLoadFileActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "exchange-load-file-actions";
    }
}
