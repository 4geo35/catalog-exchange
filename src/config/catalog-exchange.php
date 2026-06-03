<?php

return [
    // Settings
    "useQueue" => true,

    // Import XML
    "importXml" => [
        "categories" => "Классификатор->Группы->Группа",
        "subCategories" => "Группы->Группа",
        "products" => "Каталог->Товары->Товар",
    ],

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
