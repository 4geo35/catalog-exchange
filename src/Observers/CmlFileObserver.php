<?php

namespace GIS\CatalogExchange\Observers;

use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Models\CmlFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmlFileObserver
{
    public function creating(CmlFileInterface $file): void
    {
        $uuid = Str::uuid();
        $modelClass = config("catalog-exchange.customCmlFileModel") ?? CmlFile::class;
        while ($modelClass::query()->where('id', $uuid)->exists()) {
            $uuid = Str::uuid();
        }
        $file->id = $uuid;
    }

    public function deleted(CmlFileInterface $file): void
    {
        if (Storage::has($file->path)) { Storage::delete($file->path); }
    }
}
