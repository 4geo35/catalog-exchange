<?php

namespace GIS\CatalogExchange;

use GIS\CatalogExchange\Models\Cml;
use GIS\CatalogExchange\Observers\CmlObserver;
use GIS\UserManagement\Traits\ExpandPoliciesTrait;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use GIS\CatalogExchange\Livewire\Admin\Cmls\IndexWire as AdminCmlIndexWire;
use Livewire\Livewire;

class CatalogExchangeServiceProvider extends ServiceProvider
{
    use ExpandPoliciesTrait;

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

        $this->expandConfiguration();
        $this->observeModels();
        $this->setPolicies();

        $this->addLivewireComponents();
    }

    protected function expandConfiguration(): void
    {
        $cexc = app()->config["catalog-exchange"];
        $this->expandPolicies($cexc, ["cmlPolicy"]);
    }

    protected function setPolicies(): void
    {
        Gate::policy(config("catalog-exchange.customCmlModel") ?? Cml::class, config("catalog-exchange.cmlPolicy"));
    }

    protected function initFacades(): void
    {
    }

    protected function addLivewireComponents(): void
    {
        $component = config("catalog-exchange.customAdminCmlIndexComponent");
        Livewire::component(
            "cexc-admin-cml-index",
            $component ?? AdminCmlIndexWire::class
        );
    }

    protected function observeModels(): void
    {
        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        $observerClass = config("catalog-exchange.customCmlModelObserver") ?? CmlObserver::class;
        $modelClass::observe($observerClass);
    }
}
