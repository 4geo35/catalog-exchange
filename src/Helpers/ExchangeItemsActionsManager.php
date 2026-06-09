<?php

namespace GIS\CatalogExchange\Helpers;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlFileProgressBarInterface;
use GIS\CatalogExchange\Traits\ExchangeCategoryTrait;
use GIS\CatalogExchange\Traits\ExchangeProductTrait;
use GIS\CatalogExchange\Traits\ExchangeSpecificationTrait;
use GIS\CatalogExchange\Traits\ProgressBarTrait;
use GIS\CategoryProduct\Interfaces\CategoryInterface;
use GIS\CategoryProduct\Interfaces\ProductInterface;
use GIS\CategoryProduct\Interfaces\SpecificationInterface;
use GIS\CategoryProduct\Models\Category;
use GIS\CategoryProduct\Models\Product;
use GIS\CategoryProduct\Models\Specification;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class ExchangeItemsActionsManager
{
    use ProgressBarTrait, ExchangeCategoryTrait, ExchangeSpecificationTrait, ExchangeProductTrait;

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

    public function findSpecificationByUuid(string $uuid): ?SpecificationInterface
    {
        $modelClass = config("category-product.customSpecificationModel") ?? Specification::class;
        return $modelClass::query()
            ->where("exchange_uuid", $uuid)
            ->first();
    }

    public function findProductByUuid(string $uuid): ?ProductInterface
    {
        $modelClass = config("category-product.customProductModel") ?? Product::class;
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
        if (count($this->specifications)) {
            $this->setProgressBar("specifications", count($this->specifications));
            $this->processSpecifications($this->specifications);
            $this->closeProgressBar();
        }
        if ($this->products && false) { // TODO: remove false
            $this->setProgressBar("products", $this->products->count());
            $this->processProducts($this->products);
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
        $categoriesMap = ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlCategories");
        if ($categoriesMap) { $this->categories = $this->getElementByMap($this->document, $categoriesMap); }
        else { $this->categories = null; }

        $productsMap = ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlProducts");
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

    protected function getValueByMap(SimpleXMLElement $element, array $map = null, bool $toString = true): null|string|SimpleXMLElement
    {
        if (empty($map)) { return null; }
        $value = $this->getElementByMap($element, $map);
        if (empty($value)) { return null; }
        if (! $toString) { return $value; }
        try {
            $value = $value->__toString();
        } catch (\Exception $exception) {
            Log::error("Fail to get value by map (" . implode("->", $map) . ") in {$this->cmlFile->id}: " . $exception->getMessage());
            return null;
        }
        return $value;
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
