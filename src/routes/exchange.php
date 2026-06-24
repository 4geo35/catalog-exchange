<?php

use Illuminate\Support\Facades\Route;
use GIS\CatalogExchange\Facades\ExchangeProtocolActions;

Route::middleware(config("catalog-exchange.routeMiddlewareGroups"))
    ->prefix(config("catalog-exchange.routePrefix"))
    ->as("catalog-exchange.")
    ->group(function () {
        Route::match(["get", "post"], "/", function () {
            return ExchangeProtocolActions::init();
        });
    });
