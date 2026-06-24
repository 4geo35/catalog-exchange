<?php

namespace GIS\CatalogExchange\Helpers;

use GIS\CatalogExchange\Events\CompleteExchangeFileEvent;
use GIS\CatalogExchange\Events\FireExchangeFileEvent;
use GIS\CatalogExchange\Events\LaunchExchangeFileEvent;
use GIS\CatalogExchange\Events\StopExchangeFileEvent;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlInterface;
use Illuminate\Support\Facades\Log;

class ExchangeParserActionsManager
{
    public function checkFileProgress(CmlInterface $cml): string
    {
//        $result =
        return "";
    }

    public function fireParseFile(CmlFileInterface $file): void
    {
        if (! $file->can_parse) {
            $this->stopParseFile($file);
            Log::error("File {$file->id} cannot be parsed");
            return;
        }
        $file->update([
            "started_at" => now(),
            "launched_at" => null,
            "completed_at" => null,
        ]);
        FireExchangeFileEvent::dispatch($file->id);
    }

    public function stopParseFile(CmlFileInterface $file): void
    {
        $file->update([
            "completed_at" => now(),
        ]);
        StopExchangeFileEvent::dispatch($file->id);
    }

    public function launchParseFile(CmlFileInterface $file): void
    {
        $file->update([
            "launched_at" => now(),
        ]);
        LaunchExchangeFileEvent::dispatch($file);
    }

    public function completeParseFile(CmlFileInterface $file): void
    {
        $file->update([
            "completed_at" => now(),
        ]);
        CompleteExchangeFileEvent::dispatch($file);
    }

    public function getExplodedConfig(string $key): ?array
    {
        if (empty(config($key))) { return null; }
        $str = config($key);
        return explode("->", $str);
    }
}
