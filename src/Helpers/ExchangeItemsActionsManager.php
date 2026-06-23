<?php

namespace GIS\CatalogExchange\Helpers;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlFileProgressBarInterface;
use GIS\CatalogExchange\Traits\ExchangeCategoryTrait;
use GIS\CatalogExchange\Traits\ExchangeProductTrait;
use GIS\CatalogExchange\Traits\ExchangeSpecificationTrait;
use GIS\CatalogExchange\Traits\ExchangeVariationTrait;
use GIS\CatalogExchange\Traits\ProgressBarTrait;
use GIS\CategoryProduct\Interfaces\CategoryInterface;
use GIS\CategoryProduct\Interfaces\ProductInterface;
use GIS\CategoryProduct\Interfaces\SpecificationInterface;
use GIS\CategoryProduct\Models\Category;
use GIS\CategoryProduct\Models\Product;
use GIS\CategoryProduct\Models\Specification;
use GIS\ProductVariation\Interfaces\ProductVariationInterface;
use GIS\ProductVariation\Models\ProductVariation;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SimpleXMLElement;

class ExchangeItemsActionsManager
{
    use ProgressBarTrait;
    use ExchangeCategoryTrait, ExchangeSpecificationTrait, ExchangeProductTrait, ExchangeVariationTrait;

    public CmlFileInterface $cmlFile;

    public string|null $type = null;

    public SimpleXMLElement|null $document = null;
    public SimpleXMLElement|null $categories = null;
    public SimpleXMLElement|null $products = null;
    public SimpleXMLElement|null $variations = null;

    public array $specifications = [];

    public bool $onlyChanges = false;

    public function processFile(CmlFileInterface $cmlFile): void
    {
        ExchangeParserActions::launchParseFile($cmlFile);
        $this->cmlFile = $cmlFile;
        $this->initDocument();
        if (! $this->type) {
            ExchangeParserActions::completeParseFile($this->cmlFile);
            Log::error("Undefined file type, stop parsing file {$this->cmlFile->id}");
            return;
        }

        if ($this->isNeedToStop()) { return; }

        switch ($this->type) {
            case "import":
                $this->processImport();
                ExchangeParserActions::completeParseFile($this->cmlFile);
                break;

            case "offers":
                $this->processOffers();
                ExchangeParserActions::completeParseFile($this->cmlFile);
                break;

            case "yml":
                $this->processYml();
                ExchangeParserActions::completeParseFile($this->cmlFile);
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

    public function findVariationByUuid(string $uuid): ?ProductVariationInterface
    {
        $modelClass = config("product-variation.customVariationModel") ?? ProductVariation::class;
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
        if ($this->categories) {
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

        if ($this->products) {
            $this->setProgressBar("products", $this->products->count());
            $this->processProducts($this->products);
            $this->closeProgressBar();
        }

        $this->clearProducts();
        $this->clearCategories();
    }

    protected function processOffers(): void
    {
        if ($this->variations) {
            $this->setProgressBar("variations", $this->variations->count());
            $this->processVariations($this->variations);
            $this->closeProgressBar();
        }

        $this->clearVariations();
    }

    protected function processYml(): void
    {
        if ($this->categories) {
            $this->setProgressBar("categories", $this->categories->count());
            $this->processYmlCategories($this->categories);
            $this->closeProgressBar();

            $this->setProgressBar("categoryParents", $this->categories->count());
            $this->processYmlCategoryParents($this->categories);
            $this->closeProgressBar();
        }

        if ($this->products) {
            $this->setProgressBar("products", $this->products->count());
            $this->processYmlProducts($this->products);
            $this->closeProgressBar();
        }

        if ($this->variations) {
            $this->setProgressBar("variations", $this->variations->count());
            $this->processYmlVariations($this->variations);
            $this->closeProgressBar();
        }

        $this->clearProducts();
        $this->clearCategories();
        $this->clearVariations();
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
                $this->initOffers();
                break;

            case "yml":
                $this->initYml();
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

        $this->setOnlyChanges();
    }

    protected function initOffers(): void
    {
        $variationsMap = ExchangeParserActions::getExplodedConfig("catalog-exchange.offersXmlVariations");
        if ($variationsMap) { $this->variations = $this->getElementByMap($this->document, $variationsMap); }
        else { $this->variations = null; }

        $this->setOnlyChanges();
    }

    protected function initYml(): void
    {
        $categoriesMap = ExchangeParserActions::getExplodedConfig("catalog-exchange.ymlXmlCategories");
        if ($categoriesMap) { $this->categories = $this->getElementByMap($this->document, $categoriesMap); }
        else { $this->categories = null; }

        $productsMap = ExchangeParserActions::getExplodedConfig("catalog-exchange.ymlXmlProducts");
        if ($productsMap) { $this->products = $this->getElementByMap($this->document, $productsMap); }
        else { $this->products = null; }

        $this->variations = $this->products;
        $this->onlyChanges = false;
    }

    protected function setOnlyChanges(): void
    {
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

    protected function getAttributeValue(SimpleXMLElement $element, string $name): ?string
    {
        try {
            $value = $element->attributes()->{$name};
        } catch (\Exception $exception) {
            return null;
        }
        return $value;
    }

    protected function getElementValue(SimpleXMLElement $element): ?string
    {
        try {
            $value = $element->__toString();
        } catch (\Exception $exception) {
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

    protected function getTmpImageByUrl(string $url): ?UploadedFile
    {
        try {
            $response = Http::timeout(5)->get($url);
        } catch (\Exception $exception) {
            return null;
        }

        if (! $response->successful()) { return null; }

        $contents = $response->body();
        $fileName = basename(parse_url($url, PHP_URL_PATH));
        $tmpFileName = "tmp_validation_" . uniqid() . "_" . $fileName;

        try {
            Storage::disk("local")->put($tmpFileName, $contents);

            $mimeType = Storage::disk("local")->mimeType($tmpFileName);
            $tmpFilePath = Storage::disk("local")->path($tmpFileName);

            $fileInstance = new File($tmpFilePath);

            $validator = Validator::make([
                "image" => $fileInstance,
            ], [
                "image" => ["required", "image"]
            ]);

            if (!$validator->fails()) {
                return new UploadedFile($tmpFilePath, $fileName, $mimeType);
            }
        } catch (\Exception $exception) {}

        $this->clearTmlImageByName($tmpFileName);

        return null;
    }

    protected function clearTmlImageByName(string $tmpFileName): void
    {
        if (Storage::disk("local")->exists($tmpFileName)) {
            Storage::disk("local")->delete($tmpFileName);
        }
    }
}
