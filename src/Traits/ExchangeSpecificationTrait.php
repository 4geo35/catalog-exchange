<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use SimpleXMLElement;

trait ExchangeSpecificationTrait
{
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
            $item = [
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
