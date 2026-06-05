<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Interfaces\CmlFileProgressBarInterface;

trait ProgressBarTrait
{
    public CmlFileProgressBarInterface|null $progressBar = null;

    protected function setProgressBar(string $type, int $total = 0): void
    {
        $this->progressBar = $this->cmlFile
            ->progressBars()
            ->create([
                'total' => $total,
                "type" => $type,
            ]);
    }

    protected function increasePending(): void
    {
        if (! $this->progressBar) { return; }
        $pending = $this->progressBar->pending;
        $pending++;
        $this->progressBar->update([
            'pending' => $pending,
        ]);
    }

    protected function increaseTotal(int $increase): void
    {
        if (! $this->progressBar) { return; }
        $total = $this->progressBar->total;
        $total += $increase;
        $this->progressBar->update([
            'total' => $total,
        ]);
    }

    protected function closeProgressBar(): void
    {
        if (! $this->progressBar) { return; }
        $this->progressBar->update([
            "completed_at" => now()
        ]);
        $this->progressBar = null;
    }
}
