<?php

namespace GIS\CatalogExchange\Traits;

use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CategoryProduct\Models\Category;
use Illuminate\Support\Str;
use SimpleXMLElement;

trait ExchangeCategoryTrait
{
    protected function clearCategories(): void
    {
        if ($this->onlyChanges) { return; }
        $modelClass = config("category-product.customCategoryModel") ?? Category::class;
        $cmlId = $this->cmlFile->cml_id;
        $categories = $modelClass::query()
            ->where(function ($query) use ($cmlId) {
                $query
                    ->where("cml_id", "!=", $cmlId)
                    ->orWhereNull("cml_id");
            })
            ->get();
        if (! $categories->count()) { return; }

        $this->setProgressBar("clearCategories", $categories->count());
        foreach ($categories as $category) {
            if (config("catalog-exchange.deleteOldItemsOnFullExchange") && false) { // TODO: нужно ли удалять, это не безопасно
                $category->delete();
            } else {
                $category->update([
                    "published_at" => null,
                ]);
            }
            $this->increasePending();
        }
        $this->closeProgressBar();
    }

    protected function processCategories(SimpleXMLElement $categories, string $parent = null): void
    {
        $index = 0;
        foreach ($categories as $category) {
            if ($this->isNeedToStop()) { return; }

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
            "title" => $this->getValueByMap(
                $category,
                ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlCategory.title")
            ),
            "parent" => $parent,
            "priority" => $index,
        ];
    }

    protected function processCategory(object $categoryObject): void
    {
        if (empty($categoryObject->id) || empty($categoryObject->title)) { return; }
        $category = $this->findCategoryByUuid($categoryObject->id);
        $titleChanged = false;
        $title = Str::limit($categoryObject->title, 250);
        if (empty($category)) {
            $modelClass = config("category-product.customCategoryModel") ?? Category::class;
            $category = $modelClass::query()->create([
                "title" => $title,
            ]);
        } else {
            $titleChanged = $category->title !== $title;
            $category->title = $title;
        }
        $category->exchange_uuid = $categoryObject->id;
        $category->cml_id = $this->cmlFile->cml_id;
        $category->exchange_parent_uuid = $categoryObject->parent;
        if (config("catalog-exchange.applyOrder")) { $category->priority = $categoryObject->priority; }
        if (config("catalog-exchange.autoPublish")) { $category->published_at = now(); }
        if ($titleChanged && config("catalog-exchange.clearSlug")) { $category->slug = null; }
        $category->save();
    }

    protected function processCategoryParents(SimpleXMLElement $categories): void
    {
        foreach ($categories as $category) {
            if ($this->isNeedToStop()) { return; }

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
        return $this->getValueByMap(
            $category,
            ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlCategory.id")
        );
    }

    protected function getSubCategories(SimpleXMLElement $category): ?SimpleXMLElement
    {
        return $this->getValueByMap(
            $category,
            ExchangeParserActions::getExplodedConfig("catalog-exchange.importXmlSubCategories"),
            false
        );
    }
}
