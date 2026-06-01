<?php

namespace GIS\CatalogExchange;

use GIS\CatalogExchange\Models\Cml;
use GIS\CatalogExchange\Observers\CmlObserver;
use Illuminate\Support\ServiceProvider;

class CatalogExchangeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . "/database/migrations");
        $this->mergeConfigFrom(__DIR__ . "/config/catalog-exchange.php", "catalog-exchange");
        $this->initFacades();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . "/resources/views", "cexc");
        $this->loadRoutesFrom(__DIR__ . "/routes/admin.php");
        $this->addLivewireComponents();
        $this->observeModels();
    }

    protected function initFacades(): void
    {
    }

    protected function addLivewireComponents(): void
    {
    }

    protected function observeModels(): void
    {
        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        $observerClass = config("catalog-exchange.customCmlModelObserver") ?? CmlObserver::class;
        $modelClass::observe($observerClass);
    }
}
