<?php

namespace GIS\CatalogExchange\Observers;

use GIS\CatalogExchange\Interfaces\CmlInterface;
use GIS\CatalogExchange\Models\Cml;
use Illuminate\Support\Str;

class CmlObserver
{
    public function creating(CmlInterface $cml): void
    {
        $uuid = Str::uuid();
        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        while ($modelClass::query()->where('id', $uuid)->exists()) {
            $uuid = Str::uuid();
        }
        $cml->id = $uuid;
    }

    public function deleted(CmlInterface $cml): void
    {
        foreach ($cml->files as $file) {
            $file->delete();
        }
    }
}
