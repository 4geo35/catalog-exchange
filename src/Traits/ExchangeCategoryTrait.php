<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CategoryProduct\Models\Category;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

trait ExchangeCategoryTrait
{
    protected function processCategories(SimpleXMLElement $categories, string $parent = null): void
    {
        $index = 0;
        foreach ($categories as $category) {
            $this->processCategory($this->prepareCategory($category, $index, $parent));
            $this->increasePending();
            $index++;

            $subCategories = $this->getSubCategories($category);
            if (empty($subCategories)) { continue; }

            $categoryId = $this->getCategoryId($category);
            if (empty($categoryId)) { continue; }

            $this->increaseTotal($subCategories->count());
            $this->processCategories($subCategories, $categoryId);
        }
    }

    protected function prepareCategory(SimpleXMLElement $category, int $index, string $parent = null): object
    {
        return (object) [
            "id" => $this->getCategoryId($category),
            "title" => $this->getCategoryTitle($category),
            "parent" => $parent,
            "priority" => $index,
        ];
    }

    protected function processCategory(object $categoryObject): void
    {
        if (empty($categoryObject->id) || empty($categoryObject->title)) { return; }
        $category = $this->findCategoryByUuid($categoryObject->id);
        if (empty($category)) {
            $modelClass = config("category-product.customCategoryModel") ?? Category::class;
            $category = $modelClass::query()->create([
                "title" => $categoryObject->title,
            ]);
        } else {
            $category->title = $categoryObject->title;
        }
        $category->exchange_uuid = $categoryObject->id;
        $category->exchange_parent_uuid = $categoryObject->parent;
        if (config("catalog-exchange.applyOrder")) { $category->priority = $categoryObject->priority; }
        if (config("catalog-exchange.autoPublish")) { $category->published_at = now(); }
        $category->save();
    }

    protected function processCategoryParents(SimpleXMLElement $categories): void
    {
        foreach ($categories as $category) {
            $this->processParent($this->getCategoryId($category));
            $this->increasePending();

            $subCategories = $this->getSubCategories($category);
            if (empty($subCategories)) { continue; }

            $this->increaseTotal($subCategories->count());
            $this->processCategoryParents($subCategories);
        }
    }

    protected function processParent(string $id = null): void
    {
        if (empty($id)) { return; }
        $category = $this->findCategoryByUuid($id);
        $oldParent = $category->parent;
        $newParentId = $category->exchange_parent_uuid;
        // Если родитель не изменился, то выходим
        if (! empty($oldParent) && $oldParent->exchange_uuid === $newParentId) { return; }
        // Если категория стала родительской, трем родителя, если есть, и выходим
        if (! empty($oldParent) && empty($newParentId)) {
            $category->parent()->dissociate();
            $category->save();
            return;
        } // Если родитель был пустой и остался пустым, выходим
        elseif (empty($newParentId)) { return; }

        $parent = $this->findCategoryByUuid($newParentId);
        // Если не нашли родителя, чистим старого и выходим
        if (empty($parent)) {
            $category->parent()->dissociate();
            $category->save();
            return;
        }
        // Задаем нового родителя
        $category->parent()->associate($parent);
        $category->save();
    }

    protected function getCategoryId(SimpleXMLElement $category): ?string
    {
        $categoryIdMap = ExchangeParserActions::getImportCategoryIdMap();
        if (empty($categoryIdMap)) { return null; }
        $categoryId = $this->getElementByMap($category, $categoryIdMap);
        if (empty($categoryId)) { return null; }

        try {
            $categoryId = $categoryId->__toString();
        } catch (\Exception $exception) {
            Log::error("Fail to get category ID in {$this->cmlFile->id}: " . $exception->getMessage());
            return null;
        }

        return $categoryId;
    }

    protected function getCategoryTitle(SimpleXMLElement $category): ?string
    {
        $categoryTitleMap = ExchangeParserActions::getImportCategoryTitleMap();
        if (empty($categoryTitleMap)) { return null; }
        $categoryTitle = $this->getElementByMap($category, $categoryTitleMap);
        if (empty($categoryTitle)) { return null; }

        try {
            $categoryTitle = $categoryTitle->__toString();
        } catch (\Exception $exception) {
            Log::error("Fail to get category Title in {$this->cmlFile->id}: " . $exception->getMessage());
            return null;
        }

        return $categoryTitle;
    }

    protected function getSubCategories(SimpleXMLElement $category): ?SimpleXMLElement
    {
        $subCategoriesMap = ExchangeParserActions::getImportSubCategoriesMap();
        if (empty($subCategoriesMap)) { return null; }
        $subCategories = $this->getElementByMap($category, $subCategoriesMap);
        if (empty($subCategories)) { return null; }
        return $subCategories;
    }
}
