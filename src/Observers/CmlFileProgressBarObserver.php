<?php

namespace GIS\CatalogExchange\Observers;

use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlFileProgressBarInterface;
use GIS\CatalogExchange\Models\CmlFile;
use GIS\CatalogExchange\Models\CmlFileProgressBar;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmlFileProgressBarObserver
{
    public function creating(CmlFileProgressBarInterface $progressBar): void
    {
        $uuid = Str::uuid();
        $modelClass = config("catalog-exchange.customCmlFileProgressBarModel") ?? CmlFileProgressBar::class;
        while ($modelClass::query()->where('id', $uuid)->exists()) {
            $uuid = Str::uuid();
        }
        $progressBar->id = $uuid;
    }
}
