<?php

namespace GIS\CatalogExchange\Livewire\Admin\CmlFiles;

use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use Illuminate\View\View;
use Livewire\Component;

class ItemWire extends Component
{
    public CmlFileInterface $cmlFile;

    public function render(): View
    {
        $item = $this->cmlFile;
        $cml = $this->cmlFile->cml;
        $progressBars = $this->cmlFile->progressBars()->orderBy("created_at", "DESC")->get();
        return view("cexc::livewire.admin.cml-files.item-wire", compact("item", "cml", "progressBars"));
    }

    public function showRun(string $modelId): void
    {
        $this->dispatch("cexc-show-run", modelId: $modelId);
    }

    public function showStop(string $modelId): void
    {
        $this->dispatch("cexc-show-stop", modelId: $modelId);
    }

    public function showDelete(string $modelId): void
    {
        $this->dispatch("cexc-show-delete", modelId: $modelId);
    }

    public function showClearHistory(string $modelId): void
    {
        $this->dispatch("cexc-show-clear-history", modelId: $modelId);
    }
}
