<?php

namespace GIS\CatalogExchange\Facades;

use GIS\CatalogExchange\Helpers\ExchangeItemsActionsManager;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CategoryProduct\Interfaces\CategoryInterface;
use GIS\CategoryProduct\Interfaces\ProductInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void processFile(CmlFileInterface $cmlFile)
 *
 * @method static CategoryInterface|null findCategoryByUuid(string $uuid)
 * @method static CategoryInterface|null findSpecificationByUuid(string $uuid)
 * @method static ProductInterface|null findProductByUuid(string $uuid)
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
