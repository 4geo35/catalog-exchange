<?php

namespace GIS\CatalogExchange\Helpers;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlFileProgressBarInterface;
use GIS\CatalogExchange\Traits\ExchangeCategoryTrait;
use GIS\CatalogExchange\Traits\ExchangeSpecificationTrait;
use GIS\CatalogExchange\Traits\ProgressBarTrait;
use GIS\CategoryProduct\Interfaces\CategoryInterface;
use GIS\CategoryProduct\Models\Category;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class ExchangeItemsActionsManager
{
    use ProgressBarTrait, ExchangeCategoryTrait, ExchangeSpecificationTrait;

    public CmlFileInterface $cmlFile;

    public string|null $type = null;

    public SimpleXMLElement|null $document = null;
    public SimpleXMLElement|null $categories = null;
    public SimpleXMLElement|null $products = null;

    public array $specifications = [];

    public bool $onlyChanges = false;

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

    public function findCategoryByUuid(string $uuid): ?CategoryInterface
    {
        $modelClass = config("category-product.customCategoryModel") ?? Category::class;
        return $modelClass::query()
            ->where("exchange_uuid", $uuid)
            ->first();
    }

    protected function isNeedToStop(): bool
    {
        $this->cmlFile->refresh();
        return $this->cmlFile->can_run;
    }

    protected function processImport(): void
    {
        if ($this->categories && false) { // TODO: remove false
            $this->setProgressBar("categories", $this->categories->count());
            $this->processCategories($this->categories);
            $this->closeProgressBar();

            $this->setProgressBar("categoryParents", $this->categories->count());
            $this->processCategoryParents($this->categories);
            $this->closeProgressBar();
        }

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

        $this->prepareSpecifications();

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
