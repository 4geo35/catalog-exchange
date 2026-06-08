<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

trait ExchangeSpecificationTrait
{
    protected function prepareSpecifications(): void
    {
        $specificationsMap = ExchangeParserActions::getImportSpecificationsMap();
        if (! $specificationsMap) {
            $this->specifications = [];
            return;
        }
        $properties = $this->getElementByMap($this->document, $specificationsMap);
        foreach ($properties as $property) {
            $id = $this->getPropertyId($property);
            if (empty($id)) { continue; }
            $item = [
                "id" => $id,
                "title" => $this->getPropertyTitle($property),
                "values" => $this->getPropertyValues($property),
            ];
            $this->specifications[$id] = $item;
        }
    }

    protected function getPropertyValues(SimpleXMLElement $element): array
    {
        $propertyValuesMap = ExchangeParserActions::getImportSpecificationValuesMap();
        if (empty($propertyValuesMap)) { return []; }
        $propertyValues = $this->getElementByMap($element, $propertyValuesMap);
        if (empty($propertyValues)) { return []; }
        $values = [];
        foreach ($propertyValues as $value) {
            $id = $this->getPropertyValueId($value);
            if (empty($id)) { continue; }
            $value = $this->getPropertyValueValue($value);
            if (empty($value)) { continue; }
            $values[$id] = $value;
        }
        return $values;
    }

    protected function getPropertyValueId(SimpleXMLElement $property): ?string
    {
        $map = ExchangeParserActions::getImportSpecificationValueIdMap();
        if (empty($map)) { return null; }
        $propertyValueId = $this->getElementByMap($property, $map);
        if (empty($propertyValueId)) { return null; }

        try {
            $propertyValueId = $propertyValueId->__toString();
        } catch (\Exception $exception) {
            Log::error("Fail to get property value ID in {$this->cmlFile->id}: " . $exception->getMessage());
            return null;
        }

        return $propertyValueId;
    }

    protected function getPropertyValueValue(SimpleXMLElement $property): ?string
    {
        $map = ExchangeParserActions::getImportSpecificationValueValueMap();
        if (empty($map)) { return null; }
        $propertyValueValue = $this->getElementByMap($property, $map);
        if (empty($propertyValueValue)) { return null; }

        try {
            $propertyValueValue = $propertyValueValue->__toString();
        } catch (\Exception $exception) {
            Log::error("Fail to get property value Value in {$this->cmlFile->id}: " . $exception->getMessage());
            return null;
        }

        return $propertyValueValue;
    }

    protected function getPropertyId(SimpleXMLElement $property): ?string
    {
        $propertyIdMap = ExchangeParserActions::getImportSpecificationIdMap();
        if (empty($propertyIdMap)) { return null; }
        $propertyId = $this->getElementByMap($property, $propertyIdMap);
        if (empty($propertyId)) { return null; }

        try {
            $propertyId = $propertyId->__toString();
        } catch (\Exception $exception) {
            Log::error("Fail to get property ID in {$this->cmlFile->id}: " . $exception->getMessage());
            return null;
        }

        return $propertyId;
    }

    protected function getPropertyTitle(SimpleXMLElement $property): ?string
    {
        $propertyTitleMap = ExchangeParserActions::getImportSpecificationTitleMap();
        $propertyTitle = $this->getElementByMap($property, $propertyTitleMap);
        if (empty($propertyTitle)) { return null; }

        try {
            $propertyTitle = $propertyTitle->__toString();
        } catch (\Exception $exception) {
            Log::error("Fail to get property title in {$this->cmlFile->id}: " . $exception->getMessage());
            return null;
        }

        return $propertyTitle;
    }
}
