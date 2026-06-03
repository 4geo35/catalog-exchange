<?php

namespace GIS\CatalogExchange\Listeners;

use GIS\CatalogExchange\Events\FireExchangeFileEvent;
use GIS\CatalogExchange\Facades\ExchangeItemsActions;
use GIS\CatalogExchange\Models\CmlFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class FireProcessExchangeFileListener implements ShouldQueue
{
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(FireExchangeFileEvent $event): void
    {
        if (! config("catalog-exchange.useQueue")) { return; }

        $fileId = $event->cmlFileId;
        $modelClass = config("catalog-exchange.customCmlFileModel") ?? CmlFile::class;
        $cmlFile = $modelClass::find($fileId);
        if (! $cmlFile) {
            Log::error("CML file not found with id {$fileId}");
            return;
        }

        ExchangeItemsActions::processFile($cmlFile);
    }
}
