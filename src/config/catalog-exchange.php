<?php

return [
    // Models
    "customCmlModel" => null,
    "customCmlModelObserver" => null,

    "customCmlFileModel" => null,
    "customCmlFileModelObserver" => null,

    // Controllers
    "customAdminCmlController" => null,

    // Components
    "customAdminCmlIndexComponent" => null,

    // Policies
    "cmlPolicy" => \GIS\CatalogExchange\Policies\CmlPolicy::class,
    "cmlPolicyKey" => "cmls",
    "cmlPolicyTitle" => "CommerceML",
];
