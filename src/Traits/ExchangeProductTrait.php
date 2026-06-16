<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CategoryProduct\Interfaces\ProductInterface;
use GIS\CategoryProduct\Interfaces\SpecificationInterface;
use GIS\CategoryProduct\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;

trait ExchangeProductTrait
{
    protected function clearProducts(): void
    {
        if ($this->onlyChanges) { return; }
        $modelClass = config("category-product.customProductModel") ?? Product::class;
        $cmlId = $this->cmlFile->cml_id;
        $products = $modelClass::query()
            ->where(function ($query) use ($cmlId) {
                $query
                    ->where("cml_id", "!=", $cmlId)
                    ->orWhereNull("cml_id");
            })
            ->get();
        if (! $products->count()) { return; }

        $this->setProgressBar("clearProducts", $products->count());
        foreach ($products as $product) {
            if (config("catalog-exchange.deleteOldItemsOnFullExchange")) {
                $product->delete();
            } else {
                $product->update([
                    "published_at" => null,
                ]);
            }
            $this->increasePending();
        }
        $this->closeProgressBar();
    }

    protected function processProducts(SimpleXMLElement $products): void
    {
        $productIds = [];
        foreach ($products as $product) {
            if ($this->isNeedToStop()) { return; }
            $product = $this->processProduct($this->prepareProduct($product));
            if (! empty($product) && $this->onlyChanges) { $productIds[] = $product->id; }
            $this->increasePending();
        }
        // TODO: update cache
    }

    protected function prepareProduct(SimpleXMLElement $product): object
    {
        $array = [];
        foreach (config("catalog-exchange.importXmlProduct") as $key => $value) {
            $array[$key] = $this->getValueByMap(
                $product,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlProduct.{$key}")
            );
        }
        $array["specifications"] = $this->parseProps($product);
        $array["variation"] = $this->prepareProductVariationData($product);
        return (object) $array;
    }

    protected function parseProps(SimpleXMLElement $product): array
    {
        $properties = $this->getElementByMap(
            $product,
            ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlProductSpecifications")
        );
        if (empty($properties)) { return []; }

        $result = [];
        foreach ($properties as $property) {
            $propertyId = $this->getValueByMap(
                $property,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlProductSpecification.id")
            );
            if (empty($propertyId)) { continue; }

            $propertyValue = $this->getValueByMap(
                $property,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlProductSpecification.value")
            );
            if (empty($propertyValue)) { continue; }

            if (empty($result[$propertyId])) { $result[$propertyId] = []; }
            if (in_array($propertyValue, $result[$propertyId])) { continue; }
            $result[$propertyId][] = $propertyValue;
        }
        return $result;
    }

    protected function prepareProductVariationData(SimpleXMLElement $product): array
    {
        $array = [];
        foreach (config("catalog-exchange.importXmlProductVariationData") as $key => $value) {
            $array[$key] = $this->getValueByMap(
                $product,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlProductVariationData.{$key}")
            );
        }
        return $array;
    }

    protected function processProduct(object $productObject): ?ProductInterface
    {
        if (
            empty($productObject->id) ||
            empty($productObject->title) ||
            empty($productObject->categoryId)
        ) { return null; }

        $category = $this->findCategoryByUuid($productObject->categoryId);
        if (empty($category)) {
            Log::error("Category {$productObject->categoryId} not found while processing product");
            return null;
        }

        $product = $this->findProductByUuid($productObject->id);
        $titleChanged = false;
        $title = $this->prepareProductTitle($productObject->title);
        if (empty($product)) {
            $product = $category->products()->create([
                'title' => $title,
            ]);
        } else {
            $titleChanged = $product->title !== $title;
            $product->title = $title;
        }
        $product->description = $this->prepareProductDescription($productObject->description);
        $product->short = $this->prepareProductShort($productObject->short);

        $product->exchange_uuid = $productObject->id;
        $product->cml_id = $this->cmlFile->cml_id;
        $product->exchange_category = $productObject->categoryId;
        $product->exchange_variation = $productObject->variation;

        if (config("catalog-exchange.autoPublish")) { $product->published_at = now(); }
        if ($titleChanged && config("catalog-exchange.clearSlug")) { $product->slug = null; }

        if ($product->category_id !== $category->id) {
            $product->category()->associate($category);
        }

        $product->save();

        $this->processProductSpecifications($product, $productObject->specifications);

        return $product;
    }

    protected function prepareProductTitle(string $title): string
    {
        $result = Str::trim($title);
        $result = Str::deduplicate($result);
        return Str::limit($result, 250);
    }

    protected function prepareProductShort(string $short = null): ?string
    {
        if (empty($short)) { return null; }
        $result = Str::trim($short);
        $result = Str::deduplicate($result);
        return Str::limit($result, 250);
    }

    protected function prepareProductDescription(string $description = null): ?string
    {
        if (empty($description)) { return null; }
        $result = Str::trim($description);
        return Str::deduplicate($result, "  ");
    }

    protected function processProductSpecifications(ProductInterface $product, array $specifications): void
    {
        foreach ($specifications as $uuid => $values) {
            $specification = $this->findSpecificationByUuid($uuid);
            if (empty($specification)) {
                Log::error("Specification {$uuid} not found while processing product specification for product {$product->exchange_uuid}");
                continue;
            }
            $currentValues = $this->checkCurrentValuesForSpecification($product, $specification, $values);
            foreach ($values as $value) {
                if (in_array($value, $currentValues)) { continue; }
                $product->specifications()->create([
                    "value" => $value,
                    "specification_id" => $specification->id,
                ]);
            }
        }
    }

    protected function checkCurrentValuesForSpecification(ProductInterface $product, SpecificationInterface $specification, array $newValues): array
    {
        $values = $product->specifications()
            ->where("specification_id", $specification->id)
            ->get();
        $array = [];
        foreach ($values as $item) {
            if (! in_array($item->value, $newValues)) {
                $item->delete();
                continue;
            }
            $array[] = $item->value;
        }
        return $array;
    }
}
