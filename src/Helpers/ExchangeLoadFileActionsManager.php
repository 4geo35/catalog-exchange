<?php

namespace GIS\CatalogExchange\Helpers;

class ExchangeLoadFileActionsManager
{
    public function getFileTypeByName(string $name): string
    {
        if (str_contains($name, "import")) { return "import"; }
        if (str_contains($name, "offers")) { return "offers"; }
        if (str_contains($name, ".zip")) { return "zip"; }
        return "undefined";
    }
}
