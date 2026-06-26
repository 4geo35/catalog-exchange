<?php

namespace GIS\CatalogExchange\Console\Commands;

use GIS\CatalogExchange\Facades\ExchangeItemsActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Models\CmlFile;
use Illuminate\Console\Command;

class ExchangeCmlFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:exchange-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process cml file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $modelClass = config("catalog-exchange.customCmlFileModel") ?? CmlFile::class;
        $cmlFile = $modelClass::query()
            ->whereNotNull("started_at")
            ->whereNull("launched_at")
            ->whereNull("completed_at")
            ->first();
        if (!$cmlFile) { return; }
        /**
         * @var CmlFileInterface $cmlFile
         */
        $this->info("Found file {$cmlFile->id}");
        ExchangeItemsActions::processFile($cmlFile);
        $this->info("Finished exchange file {$cmlFile->id}");
    }
}
