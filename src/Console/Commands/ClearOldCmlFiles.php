<?php

namespace GIS\CatalogExchange\Console\Commands;

use GIS\CatalogExchange\Models\Cml;
use Illuminate\Console\Command;

class ClearOldCmlFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:clear-cml-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old cml files';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $days = config("catalog-exchange.oldCmlsDays", 14);
        $carbon = now()->subDays($days);
        $date = $carbon->format("Y-m-d");

        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        $cmls = $modelClass::query()
            ->where("created_at", "<", $date)
            ->get();

        foreach ($cmls as $cml) {
            $cml->delete();
        }
    }
}
