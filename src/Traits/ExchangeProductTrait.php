<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CategoryProduct\Interfaces\ProductInterface;
use GIS\CategoryProduct\Interfaces\SpecificationInterface;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

trait ExchangeProductTrait
{
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
        debugbar()->info($productIds);
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
        if (empty($product)) {
            $product = $category->products()->create([
                'title' => $productObject->title,
            ]);
        } else {
            $titleChanged = $product->title !== $productObject->title;
            $product->title = $productObject->title;
        }
        $product->description = $productObject->description;
        $product->short = $productObject->short;

        $product->exchange_uuid = $productObject->id;
        $product->cml_id = $this->cmlFile->cml_id;
        if (config("catalog-exchange.autoPublish")) { $product->published_at = now(); }
        if ($titleChanged && config("catalog-exchange.clearSlug")) { $product->slug = null; }

        if ($product->category_id !== $category->id) {
            $product->category()->associate($category);
        }

        $product->save();

        $this->processProductSpecifications($product, $productObject->specifications);

        return $product;
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
