<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CategoryProduct\Interfaces\SpecificationInterface;
use GIS\CategoryProduct\Models\Specification;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use SimpleXMLElement;

trait ExchangeSpecificationTrait
{
    protected function processSpecifications(array $specifications): void
    {
        $index = 0;
        foreach ($specifications as $specification) {
            $this->processSpecification($specification, $index);
            $this->increasePending();
            $index++;
        }
    }

    protected function processSpecification(object $specificationObject, int $index): void
    {
        if (empty($specificationObject->id) || empty($specificationObject->title)) { return; }
        $specification = $this->findSpecificationByUuid($specificationObject->id) ?? $this->checkUniqueSpecification($specificationObject);
        $titleChanged = false;
        if (empty($specification)) {
            try {
                $modelClass = config("category-product.customSpecificationModel") ?? Specification::class;
                $specification = $modelClass::query()->create([
                    "title" => $specificationObject->title,
                    "type" => config("catalog-exchange.defaultSpecificationType"),
                ]);
            } catch (Exception $exception) {
                Log::error("Error while creating specification: " . $exception->getMessage());
                return;
            }
        } else {
            $titleChanged = $specification->title !== $specificationObject->title;
            $specification->title = $specificationObject->title;
        }
        $specification->exchange_uuid = $specificationObject->id;
        if (config("catalog-exchange.applyOrder")) { $specification->priority = $index; }
        if ($titleChanged && config("catalog-exchange.clearSlug")) { $specification->slug = null; }
        $specification->save();
    }

    protected function checkUniqueSpecification(object $specificationObject): ?SpecificationInterface
    {
        $modelClass = config("category-product.customSpecificationModel") ?? Specification::class;
        return $modelClass::query()
            ->where("title", $specificationObject->title)
            ->whereNull("exchange_uuid")
            ->first();
    }

    protected function prepareSpecifications(): void
    {
        $specificationsMap = ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlSpecifications");
        if (! $specificationsMap) {
            $this->specifications = [];
            return;
        }
        $properties = $this->getElementByMap($this->document, $specificationsMap);
        foreach ($properties as $property) {
            $id = $this->getValueByMap(
                $property,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlSpecification.id")
            );
            if (empty($id)) { continue; }
            $item = (object) [
                "id" => $id,
                "title" => $this->getValueByMap(
                    $property,
                    ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlSpecification.title")
                ),
                "values" => $this->getPropertyValues($property),
            ];
            $this->specifications[$id] = $item;
        }
    }

    protected function getPropertyValues(SimpleXMLElement $element): array
    {
        $propertyValuesMap = ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlSpecificationValues");
        if (empty($propertyValuesMap)) { return []; }
        $propertyValues = $this->getElementByMap($element, $propertyValuesMap);
        if (empty($propertyValues)) { return []; }
        $values = [];
        foreach ($propertyValues as $value) {
            $id = $this->getValueByMap(
                $value,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlSpecificationValue.id")
            );
            if (empty($id)) { continue; }
            $value = $this->getValueByMap(
                $value,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlSpecificationValue.value")
            );
            if (empty($value)) { continue; }
            $values[$id] = $value;
        }
        return $values;
    }
}
