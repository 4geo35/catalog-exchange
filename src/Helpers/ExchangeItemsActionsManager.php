<?php

namespace GIS\CatalogExchange\Helpers;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlFileProgressBarInterface;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class ExchangeItemsActionsManager
{
    public CmlFileInterface $cmlFile;

    public string|null $type = null;

    public SimpleXMLElement|null $document = null;
    public SimpleXMLElement|null $categories = null;
    public SimpleXMLElement|null $products = null;

    public bool $onlyChanges = false;

    public CmlFileProgressBarInterface|null $progressBar = null;

    public function processFile(CmlFileInterface $cmlFile): void
    {
        ExchangeParserActions::launchParseFile($cmlFile);
        $this->cmlFile = $cmlFile;
        $this->initDocument();
        if (! $this->type) {
            ExchangeParserActions::completeParseFile($cmlFile);
            Log::error("Undefined file type, stop parsing file {$cmlFile->id}");
            return;
        }

        if ($this->isNeedToStop()) { return; }

        switch ($this->type) {
            case "import":
                $this->processImport();
                break;

            case "offers":
                // TODO: process offers
                break;

            case "yml":
                // TODO: process yml
                break;
        }
    }

    protected function isNeedToStop(): bool
    {
        $this->cmlFile->refresh();
        return $this->cmlFile->can_run;
    }

    protected function processImport(): void
    {
        if ($this->categories) {
            debugbar()->info($this->categories->count());
        }
    }

    protected function setCommand(string $type, int $total = 0): void
    {
        $this->progressBar = $this->cmlFile->progressBars()->create([
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

    protected function initDocument(): void
    {
        $this->readXmlFile($this->cmlFile);
        if (! $this->document) { return; }
        $this->type = $this->cmlFile->type;

        switch ($this->type) {
            case "import":
                $this->initImport();
                break;

            case "offers":
                // TODO: get offers
                break;

            case "yml":
                // TODO: parse YML
                break;

            default:
                $this->type = null;
                break;
        }
    }

    protected function initImport(): void
    {
        $categoriesMap = ExchangeParserActions::getImportCategoriesMap();
        if ($categoriesMap) { $this->categories = $this->getElementByMap($this->document, $categoriesMap); }
        else { $this->categories = null; }

        $productsMap = ExchangeParserActions::getImportProductsMap();
        if ($productsMap) { $this->products = $this->getElementByMap($this->document, $productsMap); }
        else { $this->products = null; }

        if (! empty($this->document->{"Каталог"}->attributes()->{"СодержитТолькоИзменения"})) {
            $value = $this->document->{"Каталог"}->attributes()->{"СодержитТолькоИзменения"}->__toString();
            $this->onlyChanges = $value === "true";
        }
    }

    protected function getElementByMap(SimpleXMLElement $element, array $map): ?SimpleXMLElement
    {
        foreach ($map as $value) {
            if (empty($element->{$value})) {
                $element = null;
                break;
            }
            $element = $element->{$value};
        }
        return $element;
    }

    protected function readXmlFile(CmlFileInterface $cmlFile): void
    {
        try {
            $this->document = simplexml_load_file($cmlFile->full_path);
        } catch (\Exception $e) {
            Log::error("Fail load xml file on initDocument for file {$cmlFile->id}:" . $e->getMessage());
        }
    }
}
