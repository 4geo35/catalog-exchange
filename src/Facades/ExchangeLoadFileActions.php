<?php

namespace GIS\CatalogExchange\Facades;

use GIS\CatalogExchange\Helpers\ExchangeLoadFileActionsManager;
use GIS\CatalogExchange\Interfaces\CmlInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array getAvailableFileTypes()
 * @method static string getFileTypeByName(string $name)
 *
 * @method static string|null getFileName()
 * @method static string modeLoadFile(CmlInterface $cml)
 *
 * @method static string|null checkZipFiles(CmlInterface $cml)
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
