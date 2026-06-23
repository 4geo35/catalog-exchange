<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CategoryProduct\Interfaces\ProductInterface;
use GIS\ProductVariation\Interfaces\ProductVariationInterface;
use GIS\ProductVariation\Models\MeasurementUnit;
use GIS\ProductVariation\Models\ProductVariation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;

trait ExchangeVariationTrait
{
    protected function clearVariations(): void
    {
        if ($this->onlyChanges) { return; }
        if ($this->isNeedToStop()) { return; }

        $modelClass = config("product-variation.customVariationModel") ?? ProductVariation::class;
        $cmlId = $this->cmlFile->cml_id;
        $variations = $modelClass::query()
            ->where(function ($query) use ($cmlId) {
                $query
                    ->where("cml_id", "!=", $cmlId)
                    ->orWhereNull("cml_id");
            })
            ->get();
        if (! $variations->count()) { return; }

        $this->setProgressBar("clearVariations", $variations->count());

        foreach ($variations as $variation) {
            if ($this->isNeedToStop()) { return; }
            if (config("catalog-exchange.deleteOldItemsOnFullExchange")) {
                $variation->delete();
            } else {
                $variation->update([
                    "published_at" => null
                ]);
            }
            $this->increasePending();
        }

        $this->closeProgressBar();
    }

    protected function processVariations(SimpleXMLElement $variations): void
    {
        foreach ($variations as $variation) {
            if ($this->isNeedToStop()) { return; }
            $this->processVariation($this->prepareVariation($variation));
            $this->increasePending();
        }
    }

    protected function processYmlVariations(SimpleXMLElement $variations): void
    {
        foreach ($variations as $variation) {
            if ($this->isNeedToStop()) { return; }
            $this->processVariation($this->prepareYmlVariation($variation));
            $this->increasePending();
        }
    }

    protected function prepareVariation(SimpleXMLElement $variation): object
    {
        $array = [];
        foreach (config("catalog-exchange.offersXmlVariation") as $key => $value) {
            $array[$key] = $this->getValueByMap(
                $variation,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.offersXmlVariation.{$key}")
            );
        }
        $array["price"] = $this->parsePrice($variation);
        return (object) $array;
    }

    protected function prepareYmlVariation(SimpleXMLElement $variation): object
    {
        $id = $this->getAttributeValue($variation, config("catalog-exchange.ymlXmlVariationId"));
        $array = [
            "id" => $id,
            "productId" => $id,
            "available" => $this->getAttributeValue($variation, config("catalog-exchange.ymlXmlVariationAvailable")),
        ];
        foreach (config("catalog-exchange.ymlXmlVariation") as $key => $value) {
            $array[$key] = $this->getValueByMap(
                $variation,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.ymlXmlVariation.{$key}")
            );
        }
        return (object) $array;
    }

    protected function parsePrice(SimpleXMLElement $variation): ?float
    {
        $prices = $this->getElementByMap(
            $variation,
            ExchangeParserActions::getExplodedConfig("catalog-exchange.offersXmlVariationPrices")
        );
        if (empty($prices)) { return null; }
        $result = null;
        foreach ($prices as $price) {
            $type = $this->getValueByMap(
                $price,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.offersXmlVariationPrice.type")
            );
            if (empty($type)) { continue; }

            $value = $this->getValueByMap(
                $price,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.offersXmlVariationPrice.value")
            );
            if (empty($value)) { continue; }
            $result = (float) $value;
            break;
        }
        return $result;
    }

    protected function processVariation(object $variationObject): ?ProductVariationInterface
    {
        if (
            empty($variationObject->id) ||
            empty($variationObject->productId) ||
            ! is_numeric($variationObject->price)
        ) { return null; }

        $product = $this->findProductByUuid($variationObject->productId);
        if (empty($product)) {
            Log::error("Could not find product with uuid {$variationObject->productId}");
            return null;
        }

        $variationData = $this->getExchangeVariationData($product);

        $oldPrice = $this->getVariationOldPrice($variationData, $variationObject);
        $currentPrice = $variationObject->price;
        // Если старая цена меньше или равна текущей цене, значит цена битая
        if ($oldPrice && ($oldPrice <= $currentPrice)) { $oldPrice = null; }

        $sku = $this->getVariationSku($variationData, $variationObject);
        $unitId = $this->getVariationUnit($variationData, $variationObject);
        $title = $this->prepareVariationTitle($variationObject->title);

        $variation = $this->findVariationByUuid($variationObject->id);
        $dbData = [
            "title" => $title,
            // Если не приходит артикул, то желательно не стирать его каждый раз
            "sku" => $variation ? ($sku ?? $variation->sku) : $sku,
            "price" => $currentPrice,
            "old_price" => $oldPrice,
            "sale" => $oldPrice ? now() : null,
            "unit_id" => $unitId,
        ];
        if (empty($variation)) {
            $variation = $product->variations()->create($dbData);
        } else {
            $variation->update($dbData);
        }

        $variation->exchange_uuid = $variationObject->id;
        $variation->cml_id = $this->cmlFile->cml_id;
        $variation->exchange_product = $variationObject->productId;

        $available = $this->getVariationAvailable($variationObject);
        if (is_bool($available)) {
            $variation->published_at = $available ? now() : null;
        } elseif (config("catalog-exchange.autoPublish")) {
            $variation->published_at = now();
        }

        $variation->save();

        return $variation;
    }

    protected function getVariationAvailable(object $variationObject): ?bool
    {
        if (empty($variationObject->available) && empty($variationObject->store)) { return null; }
        if (! empty($variationObject->available)) {
            $value = $variationObject->available;
        } elseif (! empty($variationObject->store)) {
            $value = $variationObject->store;
        } else {
            $value = null;
        }
        if (empty($value)) { return null; }
        return $value === "true";
    }

    protected function getVariationOldPrice(array $variationData, object $variationObject): ?float
    {
        return ! empty($variationObject->oldPrice) ? $variationObject->oldPrice : null;
    }

    protected function getVariationSku(array $variationData, object $variationObject): ?string
    {
        return ! empty($variationData["sku"]) ? Str::trim($variationData["sku"]) : null;
    }

    protected function getVariationUnit(array $variationData, object $variationObject): ?int
    {
        if (empty($variationObject->unit)) { return null; }

        $unitTitle = $variationObject->unit;
        $unitTitle = Str::trim($unitTitle);
        $unitTitle = Str::deduplicate($unitTitle);
        $unitTitle = Str::limit($unitTitle, 250);

        $modelClass = config("product-variation.customUnitModel") ?? MeasurementUnit::class;
        $unit = $modelClass::query()
            ->where('title', $unitTitle)
            ->first();
        if (empty($unit)) {
            $unit = $modelClass::query()->create([
                "title" => $unitTitle,
            ]);
        }
        return $unit->id;
    }

    protected function getExchangeVariationData(ProductInterface $product): array
    {
        $json = $product->exchange_variation;
        if (is_array($json)) { return $json; } // Это если указать casts в товаре
        if (empty($json)) { return []; }
        $array = json_decode($json, true);
        if (empty($array)) { return []; }
        return $array;
    }

    protected function prepareVariationTitle(string $title = null): ?string
    {
        if (empty($title)) { return null; }
        $result = Str::trim($title);
        $result = Str::deduplicate($result);
        return Str::limit($result, 250);
    }
}
