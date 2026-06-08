<?php

namespace GIS\CatalogExchange\Facades;

use GIS\CatalogExchange\Helpers\ExchangeParserActionsManager;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void fireParseFile(CmlFileInterface $file)
 * @method static void stopParseFile(CmlFileInterface $file)
 * @method static void launchParseFile(CmlFileInterface $file)
 * @method static void completeParseFile(CmlFileInterface $file)
 *
 * @method static array|null getExplodedConfig(string $key)
 *
 * @see ExchangeParserActionsManager
 */
class ExchangeParserActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "exchange-parser-actions";
    }
}
