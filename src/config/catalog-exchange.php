<?php

return [
    // Settings
    "useQueue" => true,
    "applyOrder" => true,
    "autoPublish" => true,
    "clearSlug" => true,
    "defaultSpecificationType" => "checkbox",
    "deleteOldItemsOnFullExchange" => false, // Если true удалит все чего не нашлось в файле, иначе снимет с публикации

    // Import XML TODO: change to env
    // Categories
    "importXmlCategories" => "Классификатор->Группы->Группа",
    "importXmlSubCategories" => "Группы->Группа",
    "importXmlCategory" => [
        "id" => "Ид",
        "title" => "Наименование",
    ],
    // Specifications
    "importXmlSpecifications" => "Классификатор->Свойства->Свойство",
    "importXmlSpecification" => [
        "id" => "Ид",
        "title" => "Наименование",
    ],
    "importXmlSpecificationValues" => "ВариантыЗначений->Справочник",
    "importXmlSpecificationValue" => [
        "id" => "ИдЗначения",
        "value" => "Значение",
    ],
    // Products
    "importXmlProducts" => "Каталог->Товары->Товар",
    "importXmlProduct" => [ // Только текстовые значения, автоматически обходит этот массив
        "id" => "Ид",
        "title" => "Наименование",
        "categoryId" => "Группы->Ид",
        "description" => "Описание",
        "short" => "КраткоеОписание",
    ],
    "importXmlProductVariationData" => [
        "sku" => "Артикул",
    ],
    "importXmlProductSpecifications" => "ЗначенияСвойств->ЗначенияСвойства",
    "importXmlProductSpecification" => [
        "id" => "Ид",
        "value" => "Значение",
    ],

    // Offers XML
    "offersXmlVariations" => "ПакетПредложений->Предложения->Предложение",
    "offersXmlVariation" => [ // Только текстовые значения, автоматически обходит этот массив
        "id" => "Ид",
        "title" => "Наименование",
        "productId" => "Ид",
        "unit" => "БазоваяЕдиница",
    ],
    "offersXmlVariationPrices" => "Цены->Цена",
    "offersXmlVariationPrice" => [
        "type" => "ИдТипаЦены",
        "value" => "ЦенаЗаЕдиницу",
        "measurement" => "Единица",
    ],

    // YML XML
    "ymlXmlCategories" => "shop->categories->category",
    "ymlXmlCategoryId" => "id",
    "ymlXmlCategoryParentId" => "parentId",

    "ymlXmlProducts" => "shop->offers->offer",

    // Models
    "customCmlModel" => null,
    "customCmlModelObserver" => null,

    "customCmlFileModel" => null,
    "customCmlFileModelObserver" => null,

    "customCmlFileProgressBarModel" => null,
    "customCmlFileProgressBarModelObserver" => null,

    // Managers
    "customExchangeLoadFileActionsManager" => null,
    "customExchangeParserActionsManager" => null,
    "customExchangeItemsActionsManager" => null,

    // Controllers
    "customAdminCmlController" => null,

    // Components
    "customAdminCmlIndexComponent" => null,
    "customAdminCmlFileListComponent" => null,
    "customAdminCmlFileItemComponent" => null,

    // Listeners
    "customFireProcessExchangeFileListener" => null,

    // Policies
    "cmlPolicy" => \GIS\CatalogExchange\Policies\CmlPolicy::class,
    "cmlPolicyKey" => "cmls",
    "cmlPolicyTitle" => "CommerceML",
];
