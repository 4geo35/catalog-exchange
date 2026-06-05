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
        foreach ($categories as $category) {
            $this->processCategory($this->prepareCategory($category, $parent));
            $this->increasePending();

            $subCategoriesMap = ExchangeParserActions::getImportSubCategoriesMap();
            if (empty($subCategoriesMap)) { continue; }
            $subCategories = $this->getElementByMap($category, $subCategoriesMap);
            if (empty($subCategories)) { continue; }

            $categoryId = $this->getCategoryId($category);
            if (empty($categoryId)) { continue; }

            $this->increaseTotal($subCategories->count());
            $this->processCategories($subCategories, $categoryId);
        }
    }

    protected function prepareCategory(SimpleXMLElement $category, string $parent = null): object
    {
        return (object) [
            "id" => $this->getCategoryId($category),
            "title" => $this->getCategoryTitle($category),
            "parent" => $parent,
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
        $category->save();
    }

    protected function getCategoryId(SimpleXMLElement $category): ?string
    {
        $categoryIdMap = ExchangeParserActions::getImportCategoryIdMap();
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
        $categoryTitle = $this->getElementByMap($category, $categoryTitleMap);
        if (empty($categoryTitle)) { return null; }

        try {
            $categoryTitle = $categoryTitle->__toString();
        } catch (\Exception $exception) {
            Log::error("Fail to get category ID in {$this->cmlFile->id}: " . $exception->getMessage());
            return null;
        }

        return $categoryTitle;
    }
}
