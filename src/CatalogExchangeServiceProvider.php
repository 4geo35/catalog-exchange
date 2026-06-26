<?php

namespace GIS\CatalogExchange;

use GIS\CatalogExchange\Console\Commands\ExchangeCmlFile;
use GIS\CatalogExchange\Events\FireExchangeFileEvent;
use GIS\CatalogExchange\Helpers\ExchangeAuthActionsManager;
use GIS\CatalogExchange\Helpers\ExchangeItemsActionsManager;
use GIS\CatalogExchange\Helpers\ExchangeLoadFileActionsManager;
use GIS\CatalogExchange\Helpers\ExchangeParserActionsManager;
use GIS\CatalogExchange\Helpers\ExchangeProtocolActionsManager;
use GIS\CatalogExchange\Interfaces\CmlInterface;
use GIS\CatalogExchange\Listeners\FireProcessExchangeFileListener;
use GIS\CatalogExchange\Models\Cml;
use GIS\CatalogExchange\Models\CmlFile;
use GIS\CatalogExchange\Models\CmlFileProgressBar;
use GIS\CatalogExchange\Observers\CmlFileObserver;
use GIS\CatalogExchange\Observers\CmlFileProgressBarObserver;
use GIS\CatalogExchange\Observers\CmlObserver;
use GIS\UserManagement\Traits\ExpandPoliciesTrait;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use GIS\CatalogExchange\Livewire\Admin\Cmls\IndexWire as AdminCmlIndexWire;
use GIS\CatalogExchange\Livewire\Admin\CmlFiles\ListWire as AdminCmlFilesListWire;
use GIS\CatalogExchange\Livewire\Admin\CmlFiles\ItemWire as AdminCmlFilesItemWire;
use Livewire\Livewire;

class CatalogExchangeServiceProvider extends ServiceProvider
{
    use ExpandPoliciesTrait;

    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . "/database/migrations");
        $this->mergeConfigFrom(__DIR__ . "/config/catalog-exchange.php", "catalog-exchange");
        $this->initFacades();
        $this->bindInterfaces();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . "/resources/views", "cexc");

        $this->loadRoutesFrom(__DIR__ . "/routes/admin.php");
        $this->loadRoutesFrom(__DIR__ . "/routes/exchange.php");

        $this->expandConfiguration();
        $this->observeModels();
        $this->setPolicies();

        $this->addLivewireComponents();

        $this->listenEvents();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExchangeCmlFile::class
            ]);
        }
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
        $this->app->singleton("exchange-load-file-actions", function () {
            $managerClass = config("catalog-exchange.customExchangeLoadFileActionsManager") ?? ExchangeLoadFileActionsManager::class;
            return new $managerClass();
        });

        $this->app->singleton("exchange-parser-actions", function () {
            $managerClass = config("catalog-exchange.customExchangeParserActionsManager") ?? ExchangeParserActionsManager::class;
            return new $managerClass();
        });

        $this->app->singleton("exchange-items-actions", function () {
            $managerClass = config("catalog-exchange.customExchangeItemsActionsManager") ?? ExchangeItemsActionsManager::class;
            return new $managerClass();
        });

        $this->app->singleton("exchange-protocol-actions", function () {
            $managerClass = config("catalog-exchange.customExchangeProtocolActionsManager") ?? ExchangeProtocolActionsManager::class;
            return new $managerClass();
        });

        $this->app->singleton("exchange-auth-actions", function () {
            $managerClass = config("catalog-exchange.customExchangeAuthActionsManager") ?? ExchangeAuthActionsManager::class;
            return new $managerClass();
        });
    }

    protected function bindInterfaces(): void
    {
        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        $this->app->bind(CmlInterface::class, $modelClass);
    }

    protected function addLivewireComponents(): void
    {
        $component = config("catalog-exchange.customAdminCmlIndexComponent");
        Livewire::component(
            "cexc-admin-cml-index",
            $component ?? AdminCmlIndexWire::class
        );

        $component = config("catalog-exchange.customAdminCmlFileListComponent");
        Livewire::component(
            "cexc-admin-cml-file-list",
            $component ?? AdminCmlFilesListWire::class
        );

        $component = config("catalog-exchange.customAdminCmlFileItemComponent");
        Livewire::component(
            "cexc-admin-cml-file-item",
            $component ?? AdminCmlFilesItemWire::class
        );
    }

    protected function observeModels(): void
    {
        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        $observerClass = config("catalog-exchange.customCmlModelObserver") ?? CmlObserver::class;
        $modelClass::observe($observerClass);

        $modelClass = config("catalog-exchange.customCmlFileModel") ?? CmlFile::class;
        $observerClass = config("catalog-exchange.customCmlFileModelObserver") ?? CmlFileObserver::class;
        $modelClass::observe($observerClass);

        $modelClass = config("catalog-exchange.customCmlFileProgressBarModel") ?? CmlFileProgressBar::class;
        $observerClass = config("catalog-exchange.customCmlFileProgressBarModelObserver") ?? CmlFileProgressBarObserver::class;
        $modelClass::observe($observerClass);
    }

    protected function listenEvents(): void
    {
        $listenerClass = config("catalog-exchange.customFireProcessExchangeFileListener") ??
            FireProcessExchangeFileListener::class;
        Event::listen(FireExchangeFileEvent::class, $listenerClass);
    }
}
