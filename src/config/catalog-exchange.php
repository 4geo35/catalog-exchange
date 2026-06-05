<?php

return [
    // Settings
    "useQueue" => true,
    "applyOrder" => true,
    "autoPublish" => true,

    // Import XML TODO: change to env
    "importXmlCategories" => "Классификатор->Группы->Группа",
    "importXmlSubCategories" => "Группы->Группа",
    "importXmlCategory" => [
        "id" => "Ид",
        "title" => "Наименование",
    ],

    "importXmlProducts" => "Каталог->Товары->Товар",

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

    // Listeners
    "customFireProcessExchangeFileListener" => null,

    // Policies
    "cmlPolicy" => \GIS\CatalogExchange\Policies\CmlPolicy::class,
    "cmlPolicyKey" => "cmls",
    "cmlPolicyTitle" => "CommerceML",
];
