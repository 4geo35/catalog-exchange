<?php

use Illuminate\Support\Facades\Route;
use GIS\CatalogExchange\Http\Controllers\Admin\CmlController;

Route::middleware(["web", "auth", "app-management"])
    ->prefix("admin")
    ->as("admin.")
    ->group(function () {
        Route::prefix("cmls")
            ->as("cmls.")
            ->group(function () {
                $controllerClass = config("catalog-exchange.customAdminCmlController") ?? CmlController::class;
                Route::get("/", [$controllerClass, "index"])->name("index");
            });
    });
