<?php

namespace GIS\CatalogExchange\Helpers;

use GIS\CatalogExchange\Events\FireExchangeFileEvent;
use GIS\CatalogExchange\Events\StopExchangeFileEvent;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;

class ExchangeParserActionsManager
{
    public function fireParseFile(CmlFileInterface $file): void
    {
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
}
